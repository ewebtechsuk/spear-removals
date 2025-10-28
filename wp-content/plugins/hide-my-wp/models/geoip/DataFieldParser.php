<?php

class HMWP_Models_Geoip_DataFieldParser {

	private $handle;
	private $sectionOffset;

	public function init( $handle, $sectionOffset = null ) {
		$this->handle        = $handle;
		$this->sectionOffset = $sectionOffset === null ? $this->handle->getPosition() : $sectionOffset;

		return $this;
	}

	public function processControlByte() {
		return HMWP_Classes_ObjController::getClass( 'HMWP_Models_Geoip_ControlByte' )->consume( $this->handle );
	}

	private function readStandardField( $controlByte ) {

		$size = $controlByte->getSize();
		if ( $size === 0 ) {
			return '';
		}

		return $this->handle->read( $size );
	}

	private function parseUtf8String( $controlByte ) {
		return $this->readStandardField( $controlByte );
	}

	private function parseUnsignedInteger( $controlByte ) {
		//TODO: Does this handle large-enough values gracefully?
		return HMWP_Classes_ObjController::getClass( 'HMWP_Models_Geoip_IntegerParser' )->parseUnsigned( $this->handle, $controlByte->getSize() );
	}

	private function parseMap( $controlByte ) {

		$map = array();
		for ( $i = 0; $i < $controlByte->getSize(); $i ++ ) {
			$keyByte = $this->processControlByte();

			$key = $this->parseField( $keyByte );
			if ( ! is_string( $key ) ) {
				throw new \Exception( 'Map keys must be strings, received ' . $keyByte . ' / ' . print_r( $key, true ) . ', map: ' . print_r( $map, true ) );
			}

			$value       = $this->parseField();
			$map[ $key ] = $value;

		}

		return $map;
	}

	private function parseArray( $controlByte ) {
		$array = array();
		for ( $i = 0; $i < $controlByte->getSize(); $i ++ ) {
			$array[ $i ] = $this->parseField();
		}

		return $array;
	}

	private function parseBoolean( $controlByte ) {
		return (bool) $controlByte->getSize();
	}

	private static function unpackSingleValue( $format, $data, $controlByte ) {
		$values = unpack( $format, $data );
		if ( $values === false ) {
			throw new \Exception( "Unpacking field failed for {$controlByte}" );
		}

		return reset( $values );
	}

	private static function getPackedLength( $formatCharacter ) {
		switch ( $formatCharacter ) {
			case 'E':
				return 8;
			case 'G':
			case 'l':
				return 4;
		}
		throw new InvalidArgumentException( "Unsupported format character: {$formatCharacter}" );
	}

	private static function usesSystemByteOrder( $formatCharacter ) {
		switch ( $formatCharacter ) {
			case 'l':
				return true;
			default:
				return false;
		}
	}

	private function parseByUnpacking( $controlByte, $format ) {
		//TODO: Is this reliable for float/double types, considering that the size for unpack is platform dependent?
		$data = $this->readStandardField( $controlByte );
		$data = str_pad( $data, self::getPackedLength( $format ), "\0", STR_PAD_LEFT );
		if ( self::usesSystemByteOrder( $format ) ) {

			/** @var HMWP_Models_Geoip_Endianness $Endianness */
			$Endianness = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Geoip_Endianness' );

			$data = $Endianness::convert( $data, $Endianness::BIG );
		}

		return $this->unpackSingleValue( $format, $data, $controlByte );
	}

	private function parsePointer( $controlByte ) {
		$data    = $controlByte->getSize();
		$size    = $data >> 3;
		$address = $data & 7;
		if ( $size === 3 ) {
			$address = 0;
		}
		for ( $i = 0; $i < $size + 1; $i ++ ) {
			$address = ( $address << 8 ) + $this->handle->readByte();
		}
		switch ( $size ) {
			case 1:
				$address += 2048;
				break;
			case 2:
				$address += 526336;
				break;
		}
		$previous = $this->handle->getPosition();
		$this->handle->seek( $this->sectionOffset + $address, SEEK_SET );
		$referenceControlByte = $this->processControlByte();

		if ( $referenceControlByte->getType() === $controlByte::TYPE_POINTER ) {
			throw new \Exception( 'Per the MMDB specification, pointers may not point to other pointers. This database does not comply with the specification.' );
		}

		$value = $this->parseField( $referenceControlByte );
		$this->handle->seek( $previous, SEEK_SET );

		return $value;
	}

	private function parseSignedInteger( $controlByte, $format ) {
		if ( $controlByte->getSize() === 0 ) {
			return 0;
		}

		return $this->parseByUnpacking( $controlByte, $format );
	}

	public function parseField( &$cByte = null ) {

		/** @var HMWP_Models_Geoip_ControlByte $controlByte */
		$controlByte = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Geoip_ControlByte' );

		if ( $cByte === null ) {
			$cByte = $this->processControlByte();
		}

		switch ( $cByte->getType() ) {
			case $controlByte::TYPE_POINTER:
				return $this->parsePointer( $cByte );
			case $controlByte::TYPE_UTF8_STRING:
				return $this->parseUtf8String( $cByte );
			case $controlByte::TYPE_DOUBLE:
				return $this->parseByUnpacking( $cByte, 'E' );
			case $controlByte::TYPE_BYTES:
			case $controlByte::TYPE_CONTAINER:
				return $this->readStandardField( $cByte );
			case $controlByte::TYPE_UINT16:
			case $controlByte::TYPE_UINT32:
			case $controlByte::TYPE_UINT64:
			case $controlByte::TYPE_UINT128:
				return $this->parseUnsignedInteger( $cByte );
			case $controlByte::TYPE_INT32:
				return $this->parseSignedInteger( $cByte, 'l' );
			case $controlByte::TYPE_MAP:
				return $this->parseMap( $cByte );
			case $controlByte::TYPE_ARRAY:
				return $this->parseArray( $cByte );
			case $controlByte::TYPE_END_MARKER:
				return null;
			case $controlByte::TYPE_BOOLEAN:
				return $this->parseBoolean( $cByte );
			case $controlByte::TYPE_FLOAT:
				return $this->parseByUnpacking( $cByte, 'G' );
			default:
				throw new \Exception( "Unable to parse data field for {$cByte}" );
		}
	}

}