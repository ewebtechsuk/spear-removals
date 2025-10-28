<?php
/**
 * Compatibility Class
 *
 * @file The Wordfence Model file
 * @package HMWP/Compatibility/Wordfence
 * @since 7.0.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class HMWP_Models_Compatibility_Wordfence extends HMWP_Models_Compatibility_Abstract {

    /** @var array Wordfence config cache */
	public static $config = array();

	public function __construct() {
		parent::__construct();

        // Adding filters for initializing and hiding URLs during Wordfence scan
		add_filter( 'hmwp_process_init', array( $this, 'checkWordfenceScan' ) );
		add_filter( 'hmwp_process_hide_urls', array( $this, 'checkWordfenceScan' ) );

        // Adding actions for various Wordfence menu items in the admin dashboard
        add_action( 'init', function() {
			if ( is_admin() ) {

				//Add the Wordfence menu when the wp-admin path is changed
				if ( is_multisite() ) {
					if ( class_exists( 'wfUtils' ) && ! wfUtils::isAdminPageMU() ) {
						add_action( 'network_admin_menu', 'wordfence::admin_menus', 10 );
						add_action( 'network_admin_menu', 'wordfence::admin_menus_20', 20 );
						add_action( 'network_admin_menu', 'wordfence::admin_menus_30', 30 );
						add_action( 'network_admin_menu', 'wordfence::admin_menus_40', 40 );
						add_action( 'network_admin_menu', 'wordfence::admin_menus_50', 50 );
						add_action( 'network_admin_menu', 'wordfence::admin_menus_60', 60 );
						add_action( 'network_admin_menu', 'wordfence::admin_menus_70', 70 );
						add_action( 'network_admin_menu', 'wordfence::admin_menus_80', 80 );
						add_action( 'network_admin_menu', 'wordfence::admin_menus_90', 90 );
					} //else don't show the menu
				}
			}
		} );

        // Checking if brute force protection with captcha is enabled
        if ( HMWP_Classes_Tools::getOption( 'hmwp_bruteforce' ) && HMWP_Classes_Tools::getOption( 'brute_use_captcha_v3' ) ) {
            // Adding compatibility to not load brute force when 2FA is active
            if ( $this->wfIs2FA() ) {
				//Add compatibility with Wordfence to not load the Bruteforce when 2FA is active
				add_filter( 'hmwp_option_brute_use_captcha_v3', '__return_false' );
			}
		}

        // Adding actions for handling Wordfence scan
		add_action( 'wf_scan_monitor', array( $this, 'witelistWordfence' ) );
		add_action( 'wordfence_start_scheduled_scan', array( $this, 'witelistWordfence' ) );

		//Add local IPs in whitelist
		add_filter( 'hmwp_rules_whitelisted_ips', function ( $ips ) {
			// Set known logged in cookies
			$domain = ( HMWP_Classes_Tools::isMultisites() && defined( 'BLOG_ID_CURRENT_SITE' ) ) ? get_home_url( BLOG_ID_CURRENT_SITE ) : site_url();
			if ( filter_var( $domain, FILTER_VALIDATE_URL ) !== false && strpos( $domain, '.' ) !== false ) {
				if ( ! HMWP_Classes_Tools::isLocalFlywheel() ) {
					$ips[] = '127.0.0.1';

					//set local domain IP
					if( $local_ip = get_transient('hmwp_local_ip') ){
						$ips[] = $local_ip;
					}elseif( $local_ip = @gethostbyname( wp_parse_url($domain, PHP_URL_HOST) ) ) {
						set_transient( 'hmwp_local_ip', $local_ip );
						$ips[] = $local_ip;
					}

				}
			}

			return $ips;
		});
	}

    /**
     * Retrieves the configuration value for a given key from the wfconfig table.
     *
     * @param  string  $key  The key for the configuration value to retrieve.
     *
     * @return mixed The configuration value if found, otherwise false.
     */
	public function wfConfig( $key ) {
        // Make $wpdb instance available
        global $wpdb;

        // Check if the configuration for the given key already exists in self::$config
        if ( isset( self::$config[ $key ] ) ) {
			return self::$config[ $key ];
            // Return the stored configuration value
		}

        // Define the table name by concatenating the base prefix and 'wfconfig'
        $table = $wpdb->base_prefix . 'wfconfig';
        // Check if the wfconfig table exists in the current database
        if ( $wpdb->get_col( $wpdb->prepare( "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s", $table ) ) ) {
            // Query the table for the row that matches the given key
            if ( $option = $wpdb->get_row( $wpdb->prepare( "SELECT name, val, autoload FROM $table WHERE name = %s", $key ) ) ) {

                // Check if the value exists in the result
                if ( isset( $option->val ) ) {
                    // Store the value in self::$config for future use
                    self::$config[ $key ] = $option->val;

                    // Return the value
					return $option->val;
				}
			}
		}

        // If the value is not found, return false
        return false;
	}

    /**
     * Checks whether the 2FA (Two-Factor Authentication) table exists and has at least one entry.
     *
     * @return bool Returns true if the 2FA table exists and contains at least one row, otherwise false.
     */
	public function wfIs2FA() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'wfls_2fa_secrets';

        // Check if the 2FA secrets table exists
        $checkTableQuery = $wpdb->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = %s", $table);
        $tableExists = $wpdb->get_col($checkTableQuery);

        if ($tableExists) {
            // Check if there is any record in the 2FA secrets table
            $checkRecordQuery = $wpdb->prepare("SELECT id FROM $table LIMIT %d", 1);
            $recordExists = $wpdb->get_row($checkRecordQuery);

            if ($recordExists) {
                return true;
            }
        }

        return false;
	}

    /**
     * Check and handle Wordfence scan status.
     *
     * @param  bool  $status  The current status of the scan.
     *
     * @return bool The updated status of the scan.
     */
	public function checkWordfenceScan( $status ) {

        // Check if the scan is starting manually via cron or AJAX
        if(  HMWP_Classes_Tools::isCron() || HMWP_Classes_Tools::isAjax()){

            // Check if the action is Wordfence scan
            if('wordfence_scan' == HMWP_Classes_Tools::getValue( 'action' )){

                // Whitelist Wordfence and disable hiding URLs
                $this->witelistWordfence();
                $status = false;
            }

            // If scan is running or hiding URLs is disabled, update status
            if ( $this->isRunning() || get_transient( 'hmwp_disable_hide_urls' ) ) {
                $status = false;
            }

        }elseif( ! $this->isRunning() ){
            // Delete the transient if scan is not running
            delete_transient( 'hmwp_disable_hide_urls' );
        }

		return $status;
	}

    /**
     * Temporarily disables URL hiding in the Wordfence plugin
     *
     * @return void
     */
	public function witelistWordfence() {
		set_transient( 'hmwp_disable_hide_urls', 1, HOUR_IN_SECONDS );
	}

    /**
     * Check if a scan is currently running.
     *
     * @return bool True if a scan is running, false otherwise.
     */
    public function isRunning() {
        $scanRunning = $this->wfConfig( 'wf_scanRunning' );
        $scanStarted = $this->wfConfig( 'scanStartAttempt' );
        return ($scanStarted || $scanRunning);
    }


}
