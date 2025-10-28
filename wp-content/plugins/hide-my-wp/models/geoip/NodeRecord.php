<?php

class HMWP_Models_Geoip_NodeRecord {

	private $reader;
	private $value;

	public function init( $reader, $value ) {
		$this->reader = $reader;
		$this->value  = $value;

		return $this;
	}

	public function getValue() {
		return $this->value;
	}

	public function isNodePointer() {
		return $this->value < $this->reader->getNodeCount();
	}

	public function getNextNode() {
		if ( ! $this->isNodePointer() ) {
			throw new \Exception( 'The next node was requested for a record that is not a node pointer' );
		}

		try {
			return $this->reader->read( $this->getValue() );
		} catch ( \Exception $e ) {
			throw new \Exception( 'Invalid node pointer found in database', $e );
		}

	}

	public function isNullPointer() {
		return $this->value === $this->reader->getNodeCount();
	}

	public function isDataPointer() {
		return $this->value > $this->reader->getNodeCount();
	}

	public function getDataAddress() {

		if ( ! $this->isDataPointer() ) {
			throw new \Exception( 'The data address was requested for a record that is not a data pointer' );
		}

		return $this->value - $this->reader->getNodeCount() + $this->reader->getSearchTreeSectionSize();
	}

}