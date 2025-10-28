<?php
namespace JET_APB\DB;

use JET_APB\Plugin;
use JET_APB\Tools;

/**
 * Database manager class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Base DB class
 */
class Manager {

	public $appointments;
	public $appointments_meta;
	public $excluded_dates;
	public $dates_to_exclude = array();

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		$this->appointments      = new Appointments();
		$this->appointments_meta = new Appointments_Meta();
		$this->excluded_dates    = new Excluded_Dates();

		// Adjust excluded dates state on appointments status switch from valid to invalid and vice versa
		add_action( 'jet-apb/db/update/appointments', [ $this, 'adjust_excluded_dates' ], 10, 3 );

	}

	/**
	 * Adjust excluded dates state on appointments status switch from valid to invalid and vice versa
	 * 
	 * @param  [type] $new_appointment [description]
	 * @param  [type] $update_by       [description]
	 * @param  [type] $old_appointment [description]
	 * @return [type]                  [description]
	 */
	public function adjust_excluded_dates( $new_appointment, $update_by, $old_appointment ) {

		$new_status = ! empty( $new_appointment['status'] ) ? $new_appointment['status'] : false;
		$old_status = ! empty( $old_appointment['status'] ) ? $old_appointment['status'] : false;

		if ( in_array( $new_status, Plugin::instance()->statuses->invalid_statuses() ) 
			&& in_array( $old_status, Plugin::instance()->statuses->exclude_statuses() )
		) {
			Plugin::instance()->db->remove_appointment_date_from_excluded( $new_appointment );
		}
		
		if ( in_array( $old_status, Plugin::instance()->statuses->invalid_statuses() ) 
			&& in_array( $new_status, Plugin::instance()->statuses->exclude_statuses() ) 
		) {
			Plugin::instance()->db->maybe_exclude_appointment_date( $new_appointment );
		}

	}

	/**
	 * Remove date of passed appoinemtnt from excluded dates
	 *
	 * @param  [type] $appointment [description]
	 * @return [type]              [description]
	 */
	public function remove_appointment_date_from_excluded( $appointment ) {

		if ( is_integer( $appointment ) ) {
			$appointment = $this->get_appointment_by( 'ID', $appointment );
		}

		if ( ! $appointment ) {
			return;
		}

		$excluded_where = array();

		if ( ! empty( $appointment['date'] ) ) {
			$excluded_where['date'] = $appointment['date'];
		}

		if ( ! empty( $appointment['service'] ) ) {
			$excluded_where['service'] = $appointment['service'];
		}

		if ( ! empty( $appointment['provider'] ) ) {
			$excluded_where['provider'] = $appointment['provider'];
		}

		$this->excluded_dates->delete( $excluded_where );

	}

	/**
	 * Check if date of given appointments is in schedule
	 * 
	 * @param  array  $appointment [description]
	 * @return boolean              [description]
	 */
	public function is_appointment_date_allowed( $appointment = [] ) {

		$date     = $appointment['date'] ?? null;
		$service  = $appointment['service'] ?? null;
		$provider = $appointment['provider'] ?? null;

		if ( ! $date ) {
			return false;
		}

		$excluded_dates      = Plugin::instance()->calendar->get_off_dates( $service, $provider );
		$works_dates         = Plugin::instance()->calendar->get_works_dates( $service, $provider );
		$allowed_dates_range = Plugin::instance()->calendar->get_dates_range( $service, $provider );
		$dates_mode          = Plugin::instance()->calendar->get_working_days_mode( $service, $provider );
		$available_week_days = Plugin::instance()->calendar->get_available_week_days( $service, $provider );

		// If date not in allowed range - decline it in any case
		if ( ! empty( $allowed_dates_range ) ) {
			
			if ( ! empty( $allowed_dates_range['start'] ) && $date < $allowed_dates_range['start'] ) {
				return false;
			}

			if ( ! empty( $allowed_dates_range['end'] ) && $date > $allowed_dates_range['end'] ) {
				return false;
			}
		}

		// Check if this date is set separetely as work date
		$works_dates  = ( ! empty( $works_dates ) && is_array( $works_dates ) ) ? $works_dates : [];
		$is_work_date = false;

		if ( empty( $works_dates ) || empty( $works_dates[0]['start'] ) ) {
			$is_work_date = true;
		}

		foreach ( $works_dates as $works_date ) {
			if ( $works_date['start'] <= $date && $date <= $works_date['end'] ) {
				$is_work_date = true;
			}
		}

		// if date is in $excluded_dates - decline full dates, check other - if they are not in $work_dates - also declie
		$excluded_dates  = ( ! empty( $excluded_dates ) && is_array( $excluded_dates ) ) ? $excluded_dates : [];

		foreach ( $excluded_dates as $excluded_date ) {
			if ( $excluded_date['start'] <= $date 
				&& $date <= $excluded_date['end'] 
				&& ( empty( $excluded_date['service'] ) 
					|| absint( $excluded_date['service'] ) === absint( $service ) )
				) {

				// Decline full date imideately
				if ( ! empty( $excluded_date['is_full'] ) ) {
					return false;
				}

				// Also ecline if this not is separetely set working date
				if ( ! $is_work_date ) {
					return false;
				}

			}
		}

		// If this date is set separetely as work date - allow it
		if ( $is_work_date ) {
			return true;
		} elseif ( 'override_full' === $dates_mode ) {
			return false;
		}

		// Decline not allowed week days
		if ( ! empty( $available_week_days ) ) {

			if ( ! in_array( strtolower( date( 'l', $date ) ), $available_week_days ) ) {
				return false;
			}
		}

		// Allow other any other date
		return true;

	}

	/**
	 * Check if this appointmetn is available
	 *
	 * @param  [type] $appointment_data [description]
	 * @return [type]                   [description]
	 */
	public function appointment_available( $appointment ) {
		
		$query_args   = [];
		$is_available = false;
		$service_id   = ! empty( $appointment['service'] ) ? $appointment['service'] : null;
		$provider_id  = ! empty( $appointment['provider'] ) ? $appointment['provider'] : null;
		$buffer_before = Tools::get_time_settings( $service_id, $provider_id, 'buffer_before', 0 );
		$buffer_after = Tools::get_time_settings( $service_id, $provider_id, 'buffer_after', 0 );

		if ( ! $this->is_appointment_date_allowed( $appointment ) ) {
			return false;
		}

		$appointment['slot'] -= intval( $buffer_before );
		$appointment['slot_end'] += intval( $buffer_after );
		
		if ( ! empty( $service_id ) && 'service' === Plugin::instance()->settings->get( 'check_by' ) ) {
			$query_args['service'] = $service_id;
		}

		if ( ! empty( $provider_id ) ) {
			$query_args['provider'] = $provider_id;
		}
		
		$query_args['date']     = ! empty( $appointment['date'] ) ? $appointment['date'] : null;
		$query_args['status']   = array_merge( Plugin::instance()->statuses->exclude_statuses() );
		
		$manage_capacity = Plugin::instance()->settings->get( 'manage_capacity' );
		
		if ( $manage_capacity ) {
			$booked_appointments = Plugin::instance()->db->appointments->query_with_capacity( $query_args );
			$service_count       = Plugin::instance()->tools->get_service_count( $service_id );
		} else {
			$booked_appointments = Plugin::instance()->db->appointments->query( $query_args );
		}
		
		if ( ! empty( $booked_appointments ) ) {
			$appointment_range = range( $appointment['slot'], $appointment['slot_end'] );
			foreach ( $booked_appointments as $booked_appointment ){
				if ( in_array( $booked_appointment['slot'] - $buffer_before + 1, $appointment_range )
					|| in_array( $booked_appointment['slot_end'] + $buffer_after - 1, $appointment_range )
				) {

					if ( ( 'slot' === $appointment['type'] || 'recurring' === $appointment['type'] ) && $manage_capacity ) {
						$slot_count = !empty( $booked_appointment['slot_count'] ) ? absint( $booked_appointment['slot_count'] ) : 1;
						$slot_count += !empty( $appointment['count'] ) ? absint( $appointment['count'] ) : 1;
					
						if ( $slot_count > $service_count ) {
							$is_available = true;

							break;
						}
					} else {
						$is_available = true;

						break;
					}
				}
			}
		}

		if ( ! $is_available ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Delete appointment from DB
	 *
	 * @param  [type] $appointment_id [description]
	 * @return [type]                 [description]
	 */
	public function delete_appointment( $appointment_id ) {

		$appointment = $this->get_appointment_by( 'ID', $appointment_id );

		if ( ! $appointment ) {
			return;
		}

		$appointment_where = array(
			'ID' => $appointment_id,
		);

		$this->appointments->delete( $appointment_where );
		
		$this->appointments_meta->create_table( false );
		$this->appointments_meta->delete( [
			'appointment_id' => $appointment_id,
		] );

		$this->remove_appointment_date_from_excluded( $appointment );
		$this->maybe_remove_excluded_app( $appointment );
	}

	/**
	 * Insert new appointment and maybe add excluded date
	 *
	 * @param  array  $appointment [description]
	 * @return [type]              [description]
	 */
	public function add_appointment( $appointment = array() ) {

		if ( empty( $appointment['user_id'] ) && is_user_logged_in() ) {
			$appointment['user_id'] = get_current_user_id();
		}

		if ( empty( $appointment['provider'] ) ) {
			$appointment['provider'] = 0;
		}

		if ( empty( $appointment['appointment_date'] ) ) {
			$appointment['appointment_date'] = wp_date( 'Y-m-d H:i:s' );
		}

		$meta = ! empty( $appointment['meta'] ) ? $appointment['meta'] : [];

		if ( isset( $appointment['meta'] ) ) {
			unset( $appointment['meta'] );
		}

		$appointment_id = $this->appointments->insert( $appointment );

		$this->appointments_meta->create_table( false );

		foreach ( $meta as $meta_key => $meta_value ) {
			$this->appointments_meta->insert( [
				'appointment_id' => $appointment_id,
				'meta_key'       => $meta_key,
				'meta_value'     => maybe_serialize( $meta_value ),
			] );
		}

		$this->maybe_exclude_appointment_date( $appointment );

		$appointment['ID'] = $appointment_id;

		/**
		 * Trigger hook after appoinetment created
		 */
		do_action( 'jet-apb/db/create/appointments', $appointment );

		/**
		 * Trigger update hook after appoinetment created
		 */
		do_action( 'jet-apb/db/update/appointments', $appointment, true, [] );

		return $appointment_id;
	}

	/**
	 * Maybe add appointment date to excluded
	 *
	 * @param  [type] $appointment [description]
	 * @return [type]              [description]
	 */
	public function maybe_exclude_appointment_date( $appointment, $exclude_other = true ) {

		if ( is_integer( $appointment ) ) {
			$appointment = $this->get_appointment_by( 'ID', $appointment );
		}

		if ( ! $appointment ) {
			return;
		}

		$service_id       = ! empty( $appointment['service'] ) ? $appointment['service'] : null;
		$provider_id      = ! empty( $appointment['provider'] ) ? $appointment['provider'] : null;
		$date             = ! empty( $appointment['date'] ) ? $appointment['date'] : null;
		$slot             = ! empty( $appointment['slot'] ) ? $appointment['slot'] : null;

		if ( ! $slot ) {
			return;
		}

		/**
		 * If status of current appointment shouldn't be excluded from calendar - 
		 * we don't need to do any more checks
		 */
		if ( ! in_array( $appointment['status'], Plugin::instance()->statuses->exclude_statuses() ) ) {
			return;
		}

		$manage_capacity = Plugin::instance()->settings->get( 'manage_capacity' );
		$all_slots       = Plugin::instance()->calendar->get_date_slots( $service_id, $provider_id, $date );
		$all_slots       = ! empty( $all_slots ) ? $all_slots : [];
		
		if ( $manage_capacity ) {
			
			$query_args = array(
				'date'     => $date,
				'status'   => Plugin::instance()->statuses->exclude_statuses(),
			);

			if ( $service_id ){
				$query_args['service'] = $service_id;
			}

			if ( $provider_id ) {
				$query_args['provider'] = $provider_id;
			}

			$capacity       = Plugin::instance()->db->appointments->query_with_capacity( $query_args );
			$total_capacity = Plugin::instance()->tools->get_service_count( $service_id );

			foreach ( $capacity as $capacity_slot ) {

				if ( intval( ( $capacity_slot['service'] ) ) === $appointment['service'] 
					&& intval( ( $capacity_slot['provider'] ) ) === $appointment['provider'] 
					&& intval( ( $capacity_slot['slot'] ) ) === $appointment['slot'] 
					&& true === $exclude_other ) {
						array_push( $this->dates_to_exclude, array( 'slot' => $capacity_slot['slot'], 'slot_count' => $capacity_slot['slot_count'] ) );
				}

			}

			if ( ! empty( $this->dates_to_exclude )  ) {
				foreach ( $this->dates_to_exclude as $excluded_value ) {
					if ( $excluded_value['slot_count'] >= $total_capacity ) {
						unset( $all_slots[ $excluded_value['slot'] ] );
					}
				}
			}

		} elseif ( ! empty( $all_slots ) && isset( $all_slots[ $slot ] ) ) {
			unset( $all_slots[ $slot ] );
		}

		if ( empty( $all_slots ) ) {
			$this->excluded_dates->insert( array(
				'service'  => $service_id,
				'provider' => $provider_id,
				'date'     => $date,
			) );
		}

		$check_by = Plugin::instance()->settings->get( 'check_by' );

		if ( true === $exclude_other && 'global' === $check_by ) {
			$this->maybe_exclude_other_app( $appointment );
		}

	}

	public function maybe_exclude_other_app( $appointment ) {

		$providers_cpt = Plugin::instance()->settings->get( 'providers_cpt' );

		if ( ! empty( $providers_cpt ) ) {
			$services = array_flip( Plugin::instance()->tools->get_services_for_provider( $appointment['provider'] ) );
		} else {
			$services = Plugin::instance()->tools->get_posts( 'services', [
				'post_status'    => 'any',
				'posts_per_page' => -1
			] );
		}

		unset( $services[$appointment['service']] );

		if ( ! empty( $services ) ) {
			foreach ( $services as $service_id => $service_name ) {
				$appointment['service'] = $service_id;
				$this->maybe_exclude_appointment_date( $appointment, false );
			}
		}
		
	}

	public function maybe_remove_excluded_app( $appointment ) {

		if ( 0 != $appointment['provider'] ) {
			foreach ( Plugin::instance()->tools->get_services_for_provider( $appointment['provider'] ) as $service_id ) {
				if( ! Plugin::instance()->settings->providers_slot_duplicating() ) {
					foreach (  Plugin::instance()->tools->get_providers_for_service( $service_id ) as $provider_id ) {
						if ( Plugin::instance()->settings->check_date_availability( intval( $service_id ), intval( $provider_id->ID ), intval( $appointment['date'] ) ) ) {
							$this->excluded_dates->delete( [
								'service'  => $service_id,
								'provider' => $provider_id->ID,
								'date'     => $appointment['date'],
							] );
						}
					}
				} else {
					if ( Plugin::instance()->settings->check_date_availability( intval( $service_id ), intval( $appointment['provider'] ), intval( $appointment['date'] ) ) ) {
						$this->excluded_dates->delete( [
							'service'  => $service_id,
							'provider' => $appointment['provider'],
							'date'     => $appointment['date'],
						] );
					}
				}

			}
		} else {
			$services = Plugin::instance()->tools->get_posts( 'services', [
				'post_status'    => 'any',
				'posts_per_page' => -1
			] );
			foreach ( $services as $service_id => $service_value ) {
				if ( Plugin::instance()->settings->check_date_availability( intval( $service_id ), intval( $appointment['provider'] ), intval( $appointment['date'] ) ) ) {
					$this->excluded_dates->delete( [
						'service'  => $service_id,
						'date'     => $appointment['date'],
					] );
				}
			}
		}

	}

	/**
	 * Returns appointments detail by order id
	 *
	 * @return [type] [description]
	 */
	public function get_appointments_by( $field = 'ID', $value = null ) {

		$appointments = $this->appointments->query( [ $field => $value ] );

		if ( empty( $appointments ) ) {
			return false;
		}

		return $appointments;

	}

	/**
	 * Returns appointment detail by order id
	 *
	 * @return [type] [description]
	 */
	public function get_appointment_by( $field = 'ID', $value = null ) {
		
		$appointment = $this->get_appointments_by( $field, $value );
		$appointment = $this->get_appointments_meta( $appointment );

		return ! empty( $appointment ) ? $appointment[0] : false;
	}

	/**
	 * Get meta data of given appointments
	 * 
	 * @param  array  $appointments [description]
	 * @return [type]               [description]
	 */
	public function get_appointments_meta( $appointments = [] ) {

		if ( empty( $appointments ) ) {
			return [];
		}

		$ids = [];
		$appointments_by_ids = [];

		foreach ( $appointments as $app ) {
			if ( is_object( $app ) ) {
				$app = (array) $app;
			}

			$appointments_by_ids[ $app['ID'] ] = $app;
			$ids[] = $app['ID'];
		}

		$this->appointments_meta->create_table( false );

		$meta = $this->appointments_meta->query( [ 'appointment_id' => $ids ] );

		if ( ! empty( $meta ) ) {
			foreach ( $meta as $_row ) {
				if ( empty( $appointments_by_ids[ $_row['appointment_id'] ] ) ) {
					$appointments_by_ids[ $_row['appointment_id'] ]['meta'] = [];
				}

				$appointments_by_ids[ $_row['appointment_id'] ]['meta'][ $_row['meta_key'] ] = maybe_unserialize( $_row['meta_value'] );
			}

			return array_values( $appointments_by_ids );
		} else {
			return $appointments;
		}

	}

}
