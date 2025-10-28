<?php
namespace JET_APB;

use \Crocoblock\Macros_Handler;

class Macros {

	public $macros_object = null;
	private $macros_handler = null;

	public function __construct() {

		$this->macros_handler = new Macros_Handler( 'jet-apb' );
		add_filter( 'jet-apb/macros-list', [ $this, 'maybe_register_db_columns_macro' ] );
		
		$this->macros_handler->register_macros_list( $this->get_all() );

	}

	public function maybe_register_db_columns_macro( $macros ) {
		
		$db_columns = Plugin::instance()->settings->get( 'db_columns' );

		if ( ! empty( $db_columns ) ) {
			$macros['db_column'] = [
				'label' => __( 'Custom DB Column Value', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'db_column_value' ],
				'args'  => [
					'meta_key' => [
						'label'   => __( 'Column', 'jet-engine' ),
						'type'    => 'select',
						'default' => '',
						'options' => array_combine( $db_columns, $db_columns ),
					],
				],
			];
		}

		return $macros;
	}

	public function set_macros_object( $object = null ) {
		$this->macros_object = $object;
	}

	public function get_macros_object() {
		return $this->macros_object;
	}

	public function get_all( $sorted = false, $escape = false ) {
		return apply_filters( 'jet-apb/macros-list', [
			'appointmens_list' => [
				'label' => __( 'Appointments List Start', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointments_list' ],
			],
			'appointmens_list_end' => [
				'label' => __( 'Appointments List End', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointments_list_end' ],
			],
			'appointments_list' => [
				'label' => __( 'Appointments List Start', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointments_list' ],
			],
			'appointments_list_end' => [
				'label' => __( 'Appointments List End', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointments_list_end' ],
			],
			'appointments_group_count' => [
				'label' => __( 'Appointments Group Count', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointments_group_count' ],
			],
			'service_title' => [
				'label' => __( 'Service Title', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'service_title' ],
			],
			'service_link' => [
				'label' => __( 'Service Link', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'service_link' ],
			],
			'service_meta' => [
				'label' => __( 'Service Meta Field', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'service_meta' ],
				'args'  => [
					'meta_key' => [
						'label'   => __( 'Meta Field to Get', 'jet-engine' ),
						'type'    => 'text',
						'default' => '',
					],
				],
			],
			'provider_title' => [
				'label' => __( 'Provider Title', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'provider_title' ],
			],
			'provider_link' => [
				'label' => __( 'Provider Link', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'provider_link' ],
			],
			'provider_meta' => [
				'label' => __( 'Provider Meta Field', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'provider_meta' ],
				'args'  => [
					'meta_key' => [
						'label'   => __( 'Meta Field to Get', 'jet-engine' ),
						'type'    => 'text',
						'default' => '',
					],
				],
			],
			'appointment_id' => [
				'label' => __( 'Appointment ID', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointment_id' ],
			],
			'appointment_price' => [
				'label' => __( 'Appointment Price', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointment_price' ],
			],
			'appointment_user_email' => [
				'label' => __( 'Appointment User Email', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointment_user_email' ],
			],
			'appointment_start' => [
				'label' => __( 'Appointment Start Date/Time', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointment_start' ],
				'args'  => [
					'format' => [
						'label'   => __( 'Date format', 'jet-engine' ),
						'type'    => 'text',
						'default' => 'F j, Y g:i',
					],
				],
			],
			'appointment_end' => [
				'label' => __( 'Appointment End Date/Time', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointment_end' ],
				'args'  => [
					'format' => [
						'label'   => __( 'Date format', 'jet-engine' ),
						'type'    => 'text',
						'default' => 'F j, Y g:i',
					],
				],
			],
			'appointment_meta' => [
				'label' => __( 'Appointment Meta', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'appointment_meta' ],
				'args'  => [
					'meta_key' => [
						'label'   => __( 'Meta Key', 'jet-engine' ),
						'type'    => 'text',
						'default' => '',
					],
				],
			],
			'user_local_time' => [
				'label' => __( 'Appointment Time in Timezone of the User', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'user_local_time' ],
			],
			'user_local_date' => [
				'label' => __( 'Appointment Date in Timezone of the User', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'user_local_date' ],
			],
			'user_timezone' => [
				'label' => __( 'Timezone selected by the User', 'jet-appointments-booking' ),
				'cb'    => [ $this, 'user_timezone' ],
			],
		], $this );
	}

	public function appointments_list( $result = null, $args_str = null ) {
		return '';
	}

	public function appointments_list_end( $result = null, $args_str = null ) {
		return '';
	}

	public function service_title( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		$ID = $appointment['service'];
		return get_the_title( $ID );
	}

	public function service_link( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		$ID = $appointment['service'];
		return get_permalink( $ID );
	}

	public function provider_title( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		$ID = $appointment['provider'];
		return get_the_title( $ID );
	}

	public function db_column_value( $result = null, $args_str = null ) {
		$column = $args_str;
		$appointment = $this->get_macros_object();
		return isset( $appointment[ $column ] ) ? $appointment[ $column ] : '';
	}

	public function provider_link( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		$ID = $appointment['provider'];
		return get_permalink( $ID );
	}

	public function service_meta( $result = null, $args_str = null ) {
		
		$appointment = $this->get_macros_object();
		$meta_key    = $args_str;
		$ID          = $appointment['service'];

		if ( ! $ID || ! $meta_key ) {
			return '';
		}

		return get_post_meta( $ID, $meta_key, true );
	}

	public function provider_meta( $result = null, $args_str = null ) {
		
		$appointment = $this->get_macros_object();
		$meta_key    = $args_str;
		$ID          = $appointment['provider'];

		if ( ! $ID || ! $meta_key ) {
			return '';
		}

		return get_post_meta( $ID, $meta_key, true );
	}

	public function appointment_price( $result = null, $args_str = null ) {

		$appointment = $this->get_macros_object();
		$price       = isset( $appointment['price'] ) ? $appointment['price'] : false;

		if ( false === $price && ! empty( $appointment['service'] ) ) {
			$price_instance = new Appointment_Price( $appointment );
			$price_data = $price_instance->get_price();
			$price = ( is_array( $price_data ) && isset( $price_data['price'] ) ) ? $price_data['price'] : 0;
		}

		return $price;

	}

	public function appointments_group_count( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		return isset( $appointment['group_count'] ) ? absint( $appointment['group_count'] ) : 1;
	}

	public function appointment_user_email( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		return isset( $appointment['user_email'] ) ? $appointment['user_email'] : 0;
	}

	public function appointment_id( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		return $appointment['ID'];
	}
	

	public function appointment_meta( $result = null, $args_str = '' ) {

		$appointment = $this->get_macros_object();
		$meta_key    = $args_str;

		if ( ! $meta_key ) {
			return;
		}

		return isset( $appointment['meta'][ $meta_key ] ) ? $appointment['meta'][ $meta_key ] : '';

	}

	public function appointment_start( $result = null, $args_str = 'F j, Y g:i' ) {
	
		$appointment = $this->get_macros_object();

		if ( false === $args_str ) {
			$args_str = 'F j, Y g:i';
		}

		if ( false !== strpos( $args_str, 'format_date' ) ) {
			return jet_engine()->listings->filters->apply_filters( $appointment['slot'], $args_str );
		} else {
			return date_i18n( $args_str, $appointment['slot'] );
		}
		
	}

	public function appointment_end( $result = null, $args_str = 'F j, Y g:i' ) {

		$appointment = $this->get_macros_object();

		if ( false === $args_str ) {
			$args_str = 'F j, Y g:i';
		}

		if ( false !== strpos( $args_str, 'format_date' ) ) {
			return jet_engine()->listings->filters->apply_filters( $appointment['slot_end'], $args_str );
		} else {
			return date_i18n( $args_str, $appointment['slot_end'] );
		}
	}

	public function user_local_time( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		return ! empty( $appointment['meta']['user_local_time'] ) ? $appointment['meta']['user_local_time'] : $this->get_defaults( 'user_local_time' );
	}

	public function user_local_date( $result = null, $args_str = null ) {
		$appointment = $this->get_macros_object();
		return ! empty( $appointment['meta']['user_local_date'] ) ? $appointment['meta']['user_local_date'] : $this->get_defaults( 'user_local_date' );
	}

	public function user_timezone( $result = null, $args_str = null ) {
		
		$appointment = $this->get_macros_object();
		$value = ! empty( $appointment['meta']['user_timezone'] ) ? $appointment['meta']['user_timezone'] : $this->get_defaults( 'user_timezone' );
		
		return str_replace( '_', ' ', $value );
	}

	public function get_defaults( $key = '' ) {

		$appointment = $this->get_macros_object();

		switch ( $key ) {
			
			case 'user_local_time':
				$slot_time_format = Plugin::instance()->settings->get( 'slot_time_format' );
				$local_time       = date( $slot_time_format, $appointment['slot'] );

				if ( ! empty( $appointment['slot_end'] ) ) {
					$local_time .= '-' . date( $slot_time_format, $appointment['slot_end'] );
				}

				return $local_time;
			
			case 'user_local_date':
				return date_i18n( get_option( 'date_format', 'F d, Y' ), $appointment['slot'] );	

			case 'user_timezone':
				return wp_timezone()->getName();

		}

	}

	/**
	 * Do macros inside string
	 *
	 * @param  [type] $string      [description]
	 * @param  [type] $field_value [description]
	 * @return [type]              [description]
	 */
	public function do_macros( $string = '', $field_value = null ) {
		return $this->macros_handler->do_macros( $string, $field_value );
	}

}
