<?php
/**
 * Background Cron action
 *
 * @file The Cron file
 * @package HMWP/Cron
 * @since 4.0.0
 */

class HMWP_Controllers_Cron {

	/**
	 * HMWP_Controllers_Cron constructor.
	 */
	public function __construct() {
        // Add a custom interval to the WP cron schedules.
        add_filter( 'cron_schedules', array( $this, 'setInterval' ) );

        // Activate the cron job if it does not already exist.
		if ( ! wp_next_scheduled( HMWP_CRON ) ) {
            // Schedule an event to occur at the custom interval.
            wp_schedule_event( time(), 'hmwp_every_minute', HMWP_CRON );
		}
	}

    /**
     * Specify the Cron interval
     *
     * @param  array $schedules List of existing WP cron schedules.
     *
     * @return array Modified list of WP cron schedules with custom interval added.
     */
	function setInterval( $schedules ) {
        // Define a new cron schedule that runs every minute.
        $schedules['hmwp_every_minute'] = array(
			'display'  => 'every 1 minute',
			'interval' => 60 // Interval in seconds.
		);

        // Return the modified schedules.
		return $schedules;
	}

	/**
	 * Process Cron
	 *
	 * @throws Exception
	 */
	public function processCron() {
        // Check the cache plugins and modify paths in the cache files.
		HMWP_Classes_ObjController::getClass( 'HMWP_Models_Compatibility' )->checkCacheFiles();
	}


}
