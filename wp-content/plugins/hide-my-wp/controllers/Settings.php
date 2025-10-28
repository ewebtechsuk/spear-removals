<?php
/**
 * Settings Class
 * Called when the plugin setting is loaded
 *
 * @file The Settings file
 * @package HMWP/Settings
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class HMWP_Controllers_Settings extends HMWP_Classes_FrontController {

	/**
	 * List of events/actions
	 *
	 * @var $listTable HMWP_Models_ListTable
	 */
	public $listTable;

	public function __construct() {
		parent::__construct();

		// If save settings is required, show the alert
		if ( HMWP_Classes_Tools::getOption( 'changes' ) ) {
			add_action( 'admin_notices', array( $this, 'showSaveRequires' ) );
			HMWP_Classes_Tools::saveOptions( 'changes', false );
		}

		if ( ! HMWP_Classes_Tools::getOption( 'hmwp_valid' ) ) {
			add_action( 'admin_notices', array( $this, 'showPurchaseRequires' ) );
		}

		// Add the Settings class only for the plugin settings page
		add_filter( 'admin_body_class', array( HMWP_Classes_ObjController::getClass( 'HMWP_Models_Menu' ), 'addSettingsClass' ) );

		// If the option to prevent broken layout is on
		if ( HMWP_Classes_Tools::getOption( 'prevent_slow_loading' ) ) {

			//check the frontend on settings successfully saved
			add_action( 'hmwp_confirmed_settings', function() {
				//check the frontend and prevent from showing brake websites
				$url      = _HMWP_URL_ . '/view/assets/img/logo.svg?hmwp_preview=1&test=' . wp_rand( 11111, 99999 );
				$url      = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rewrite' )->find_replace_url( $url );
				$response = HMWP_Classes_Tools::hmwp_localcall( $url, array( 'redirection' => 0, 'cookies' => false ) );

				//If the plugin logo is not loading correctly, switch off the path changes
				if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) == 404 ) {
					HMWP_Classes_Tools::saveOptions( 'file_mappings', array( home_url() ) );
				}
			} );
		}

		// Save the login path on Cloud
		add_action( 'hmwp_apply_permalink_changes', function() {
			HMWP_Classes_Tools::sendLoginPathsApi();
		} );

	}

	/**
	 * Called on Menu hook
	 * Init the Settings page
	 *
	 * @return void
	 * @throws Exception
	 */
	public function init() {
		/////////////////////////////////////////////////
		// Get the current Page
		$page = HMWP_Classes_Tools::getValue( 'page' );

		if ( strpos( $page, '_' ) !== false ) {
			$tab = substr( $page, ( strpos( $page, '_' ) + 1 ) );

			if ( method_exists( $this, $tab ) ) {
				call_user_func( array( $this, $tab ) );
			}
		}
		/////////////////////////////////////////////////

		// We need that function so make sure is loaded
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			include_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( HMWP_Classes_Tools::isNginx() && HMWP_Classes_Tools::getOption( 'test_frontend' ) && HMWP_Classes_Tools::getOption( 'hmwp_mode' ) <> 'default' ) {
			$config_file = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rules' )->getConfFile();
			if ( HMWP_Classes_Tools::isLocalFlywheel() ) {
				if ( strpos( $config_file, '/includes/' ) !== false ) {
					$config_file = substr( $config_file, strpos( $config_file, '/includes/' ) + 1 );
				}
				HMWP_Classes_Error::setNotification( sprintf( esc_html__( "Local & NGINX detected. In case you didn't add the code in the NGINX config already, please add the following line. %s", 'hide-my-wp' ), '<br /><br /><code><strong>include ' . $config_file . ';</strong></code> <br /><strong><br /><a href="' . esc_url( HMWP_Classes_Tools::getOption('hmwp_plugin_website') . '/kb/how-to-setup-hide-my-wp-on-local-flywheel/' ) . '" target="_blank">' . esc_html__( "Learn how to setup on Local & Nginx", 'hide-my-wp' ) . ' >></a></strong>' ), 'notice', false );
			} else {
				HMWP_Classes_Error::setNotification( sprintf( esc_html__( "NGINX detected. In case you didn't add the code in the NGINX config already, please add the following line. %s", 'hide-my-wp' ), '<br /><br /><code><strong>include ' . $config_file . ';</strong></code> <br /><strong><br /><a href="' . esc_url( HMWP_Classes_Tools::getOption('hmwp_plugin_website') . '/kb/how-to-setup-hide-my-wp-on-nginx-server/' ) . '" target="_blank">' . esc_html__( "Learn how to setup on Nginx server", 'hide-my-wp' ) . ' >></a></strong>' ), 'notice', false );
			}
		}

		// Setting Alerts based on Logout and Error statements
		if ( get_transient( 'hmwp_restore' ) == 1 ) {
			$restoreLink = '<a href="' . esc_url( add_query_arg( array( 'hmwp_nonce' => wp_create_nonce( 'hmwp_restore_settings' ), 'action'     => 'hmwp_restore_settings' ) ) ) . '" class="btn btn-default btn-sm ml-3" />' . esc_html__( "Restore Settings", 'hide-my-wp' ) . '</a>';
			HMWP_Classes_Error::setNotification( esc_html__( 'Do you want to restore the last saved settings?', 'hide-my-wp' ) . $restoreLink );
		}

		// Show the config rules to make sure they are okay
		if ( HMWP_Classes_Tools::getValue( 'hmwp_config' ) ) {
			//Initialize WordPress Filesystem
			$wp_filesystem = HMWP_Classes_ObjController::initFilesystem();

			$config_file = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rules' )->getConfFile();
			if ( $config_file <> '' && $wp_filesystem->exists( $config_file ) ) {
				$rules = $wp_filesystem->get_contents( HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rules' )->getConfFile() );
				HMWP_Classes_Error::setNotification( '<pre>' . $rules . '</pre>' );
			}
		}

        // Check if the Geo Country database is downloaded correctly
		$database = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Geoip_GeoLocator' )->getDatabaseFilename();;

		if ( HMWP_Classes_Tools::getOption( 'hmwp_geoblock' ) && ! file_exists( _HMWP_MODEL_DIR_ . 'geoip/' . $database ) ) {
			$restoreLink = '<a href="' . esc_url( add_query_arg( array( 'hmwp_nonce' => wp_create_nonce( 'hmwp_geo_download' ), 'action' => 'hmwp_geo_download' ) ) ) . '" class="btn btn-default btn-sm ml-3" />' . esc_html__( "Click to retry the download", 'hide-my-wp' ) . '</a>';
			HMWP_Classes_Error::setNotification( esc_html__( 'The Country Blocking database has not been downloaded, preventing the GEO Security feature from being active.', 'hide-my-wp' ) . $restoreLink );
		}

		// Load the css for Settings
		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'popper' );

		if ( is_rtl() ) {
			HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'bootstrap.rtl' );
			HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'rtl' );
		} else {
			HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'bootstrap' );
		}

		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'bootstrap-select' );
		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'font-awesome' );
		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'switchery' );
		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'alert' );
		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'clipboard' );
		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'settings' );

		// Check connection with the cloud
		HMWP_Classes_Tools::checkAccountApi();

		// Show connect for activation
		if ( ! HMWP_Classes_Tools::getOption( 'hmwp_token' ) ) {
			$this->show( 'Connect' );

			return;
		}

		if ( HMWP_Classes_Tools::getOption( 'error' ) ) {
			HMWP_Classes_Error::setNotification( esc_html__( 'There is a configuration error in the plugin. Please Save the settings again and follow the instruction.', 'hide-my-wp' ) );
		}

		if ( HMWP_Classes_Tools::isWpengine() ) {
			add_filter( 'hmwp_option_hmwp_mapping_url_show', "__return_false" );
		}

		// Check compatibilities with other plugins
		HMWP_Classes_ObjController::getClass( 'HMWP_Models_Compatibility' )->getAlerts();

		// Show errors on top
		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_Error' )->hookNotices();

		echo '<meta name="viewport" content="width=640">';
		echo '<noscript><div class="alert-danger text-center py-3">' . sprintf( esc_html__( "Javascript is disabled on your browser! You need to activate the javascript in order to use %s plugin.", 'hide-my-wp' ), esc_html( HMWP_Classes_Tools::getOption( 'hmwp_plugin_name' ) ) ) . '</div></noscript>';
		$this->show( ucfirst( str_replace( 'hmwp_', '', $page ) ) );

	}

	/**
	 * Log the user event
	 *
	 * @throws Exception
	 */
	public function log() {
		$this->listTable = HMWP_Classes_ObjController::getClass( 'HMWP_Models_ListTable' );

		if ( apply_filters( 'hmwp_showlogs', true ) ) {

			$args           = $urls = array();
			$args['search'] = HMWP_Classes_Tools::getValue( 's', false );
			//If it's multisite
			if ( is_multisite() ) {
				if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {
					$sites = get_sites();
					if ( ! empty( $sites ) ) {
						foreach ( $sites as $site ) {
							$urls[] = ( _HMWP_CHECK_SSL_ ? 'https://' : 'http://' ) . rtrim( $site->domain . $site->path, '/' );
						}
					}
				}
			} else {
				$urls[] = home_url();
			}
			// Pack the urls
			$args['urls'] = wp_json_encode( array_unique( $urls ) );

			// Set the log table data
			$logs = HMWP_Classes_Tools::hmwp_remote_get( _HMWP_API_SITE_ . '/api/log', $args );

			if ( $logs = json_decode( $logs, true ) ) {

				if ( isset( $logs['error'] ) && $logs['error'] <> '' ) {

					// Check connection with the cloud on error
					HMWP_Classes_Tools::checkAccountApi();
				}

				if ( isset( $logs['data'] ) && ! empty( $logs['data'] ) ) {
					$logs = $logs['data'];
				} else {
					$logs = array();
				}

			} else {
				$logs = array();
			}

			$this->listTable->setData( $logs );
		}

	}

	/**
	 * Log the user event
	 *
	 * @throws Exception
	 */
	public function templogin() {
		if ( ! HMWP_Classes_Tools::getOption( 'hmwp_token' ) ) {
			return;
		}

		// Clear previous alerts
		HMWP_Classes_Error::clearErrors();

		if ( HMWP_Classes_Tools::getValue( 'action' ) == 'hmwp_update' && HMWP_Classes_Tools::getValue( 'user_id' ) ) {
			$user_id = HMWP_Classes_Tools::getValue( 'user_id' );

			$this->user          = get_user_by( 'ID', $user_id );
			$this->user->details = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Templogin' )->getUserDetails( $this->user );
		}

		if ( HMWP_Classes_Tools::getValue( 'hmwp_message' ) ) {
			HMWP_Classes_Error::setNotification( HMWP_Classes_Tools::getValue( 'hmwp_message', false, true ), 'success' );
		}

	}

	/**
	 * Firewall page init
	 *
	 * @return void
	 * @throws Exception
	 */
	public function twofactor() {

		if ( ! HMWP_Classes_Tools::isAdvancedpackInstalled() ) {

			add_filter( 'hmwp_getview', function( $output, $block ) {
				if ( $block == 'Twofactor' ) {
					return '<div id="hmwp_wrap" class="d-flex flex-row p-0 my-3">
                        <div class="hmwp_row d-flex flex-row p-0 m-0">
                            <div class="hmwp_col flex-grow-1 px-3 py-3 mr-2 mb-3 bg-white">
                                ' . $this->getView( 'blocks/Install' ) . '
                            </div>
                        </div>
                    </div>';
				}

				return $output;

			}, PHP_INT_MAX, 2 );
		}

	}

	/**
	 * Load media header
	 */
	public function hookHead() {
	}

	/**
	 * Show this message to notify the user when to update the settings
	 *
	 * @return void
	 * @throws Exception
	 */
	public function showSaveRequires() {
		if ( HMWP_Classes_Tools::getOption( 'hmwp_hide_plugins' ) || HMWP_Classes_Tools::getOption( 'hmwp_hide_themes' ) ) {
			global $pagenow;
			if ( $pagenow == 'plugins.php' ) {

				HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'alert' );

				?>
                <div class="notice notice-warning is-dismissible" style="margin-left: 0;">
                    <div style="display: inline-block;">
                        <form action="<?php echo esc_url( HMWP_Classes_Tools::getSettingsUrl() ) ?>" method="POST">
							<?php wp_nonce_field( 'hmwp_newpluginschange', 'hmwp_nonce' ) ?>
                            <input type="hidden" name="action" value="hmwp_newpluginschange"/>
                            <p>
								<?php echo sprintf( esc_html__( "New Plugin/Theme detected! Update %s settings to hide it. %sClick here%s", 'hide-my-wp' ), esc_html( HMWP_Classes_Tools::getOption( 'hmwp_plugin_name' ) ), '<button type="submit" style="color: blue; text-decoration: underline; cursor: pointer; background: none; border: none;">', '</button>' ); ?>
                            </p>
                        </form>

                    </div>
                </div>
				<?php
			}
		}
	}

	public function showPurchaseRequires() {
		global $pagenow;

		$expires = (int) HMWP_Classes_Tools::getOption( 'hmwp_expires' );

		if ( $expires > 0 ) {
			$error = sprintf( esc_html__( "Your %s %s license expired on %s %s. To keep your website security up to date please make sure you have a valid subscription on %s", 'hide-my-wp' ), '<strong>', HMWP_Classes_Tools::getOption( 'hmwp_plugin_name' ), gmdate( 'd M Y', $expires ), '</strong>', '<a href="' . HMWP_Classes_Tools::getCloudUrl( 'orders' ) . '" style="line-height: 30px;" target="_blank">'._HMWP_ACCOUNT_SITE_.'</a>' );

			if ( $pagenow == 'plugins.php' ) {
				$ignore_errors = (array) HMWP_Classes_Tools::getOption( 'ignore_errors' );

				if ( ! empty( $ignore_errors ) && in_array( strlen( $error ), $ignore_errors ) ) {
					return;
				}

				$url = add_query_arg( array(
					'hmwp_nonce' => wp_create_nonce( 'hmwp_ignoreerror' ),
					'action'     => 'hmwp_ignoreerror',
					'hash'       => strlen( $error )
				) );

				?>
                <div class="col-sm-12 mx-0 hmwp_notice error notice">
                    <div style="display: inline-block;"><p> <?php echo wp_kses_post( $error ) ?> </p></div>
                    <a href="<?php echo esc_url( $url ) ?>" style="float: right; color: gray; text-decoration: underline; font-size: 0.8rem;">
                        <p><?php echo esc_html__( 'ignore alert', 'hide-my-wp' ) ?></p></a>
                </div>
				<?php
			} else {
				HMWP_Classes_Error::setNotification( $error );
			}
		}
	}

	/**
	 * Get the Admin Toolbar
	 *
	 * @param null $current
	 *
	 * @return string $content
	 * @throws Exception
	 */
	public function getAdminTabs( $current = null ) {
		//Add the Menu Sub Tabs in the selected page
		$subtabs = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Menu' )->getSubMenu( $current );

		$content = '<div class="hmwp_nav d-flex flex-column bd-highlight mb-3">';
		$content .= '<div  class="m-0 px-3 pt-2 pb-3 font-dark font-weight-bold text-logo"><a href="' . esc_url( HMWP_Classes_Tools::getOption( 'hmwp_plugin_website' ) ) . '" target="_blank"><img src="' . esc_url( HMWP_Classes_Tools::getOption( 'hmwp_plugin_logo' ) ? HMWP_Classes_Tools::getOption( 'hmwp_plugin_logo' ) : _HMWP_ASSETS_URL_ . 'img/logo.svg' ) . '" class="ml-0 mr-2" style="height:35px; max-width: 140px;" alt=""></a></div>';

		foreach ( $subtabs as $tab ) {
			$content .= '<a href="#' . esc_attr( $tab['tab'] ) . '" class="m-0 px-3 py-3 font-dark hmwp_nav_item" data-tab="' . esc_attr( $tab['tab'] ) . '">' . wp_kses_post( $tab['title'] ) . '</a>';
		}

		$content .= '</div>';

		return $content;
	}

	/**
	 * Called when an action is triggered
	 *
	 * @throws Exception
	 */
	public function action() {
		parent::action();

		if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
			return;
		}

		switch ( HMWP_Classes_Tools::getValue( 'action' ) ) {
			case 'hmwp_settings':
				//Save the settings
				if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

					// Save the whitelist IPs
					$this->saveWhiteListIps();

					// Save the whitelist paths
					$this->saveWhiteListPaths();

					/**  @var $this ->model HMWP_Models_Settings */
					$this->model->savePermalinks( $_POST );
				}

				//load the after saving settings process
				if ( $this->model->applyPermalinksChanged() ) {
					HMWP_Classes_Error::setNotification( esc_html__( 'Saved' ), 'success' );

					//add action for later use
					do_action( 'hmwp_settings_saved' );
				}

				break;
			case 'hmwp_tweakssettings':
				//Save the settings
				if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
					$this->model->saveValues( $_POST );
				}

				HMWP_Classes_Tools::saveOptions( 'hmwp_disable_click_message', HMWP_Classes_Tools::getValue( 'hmwp_disable_click_message', '', true ) );
				HMWP_Classes_Tools::saveOptions( 'hmwp_disable_inspect_message', HMWP_Classes_Tools::getValue( 'hmwp_disable_inspect_message', '', true ) );
				HMWP_Classes_Tools::saveOptions( 'hmwp_disable_source_message', HMWP_Classes_Tools::getValue( 'hmwp_disable_source_message', '', true ) );
				HMWP_Classes_Tools::saveOptions( 'hmwp_disable_copy_paste_message', HMWP_Classes_Tools::getValue( 'hmwp_disable_copy_paste_message', '', true ) );
				HMWP_Classes_Tools::saveOptions( 'hmwp_disable_drag_drop_message', HMWP_Classes_Tools::getValue( 'hmwp_disable_drag_drop_message', '', true ) );

				//load the after saving settings process
				if ( $this->model->applyPermalinksChanged() ) {
					HMWP_Classes_Error::setNotification( esc_html__( 'Saved' ), 'success' );

					//add action for later use
					do_action( 'hmwp_tweakssettings_saved' );
				}

				break;
			case 'hmwp_mappsettings':
				//Save Mapping for classes and ids
				HMWP_Classes_Tools::saveOptions( 'hmwp_mapping_classes', HMWP_Classes_Tools::getValue( 'hmwp_mapping_classes' ) );
				HMWP_Classes_Tools::saveOptions( 'hmwp_mapping_file', HMWP_Classes_Tools::getValue( 'hmwp_mapping_file' ) );
				HMWP_Classes_Tools::saveOptions( 'hmwp_file_cache', HMWP_Classes_Tools::getValue( 'hmwp_file_cache' ) );

				//Save the patterns as array
				//Save CDN URLs
				if ( $urls = HMWP_Classes_Tools::getValue( 'hmwp_cdn_urls' ) ) {
					$hmwp_cdn_urls = array();
					foreach ( $urls as $row ) {
						if ( $row <> '' ) {
							$row = preg_replace( '/[^A-Za-z0-9-_.:\/]/', '', $row );
							if ( $row <> '' ) {
								$hmwp_cdn_urls[] = $row;
							}
						}
					}
					HMWP_Classes_Tools::saveOptions( 'hmwp_cdn_urls', wp_json_encode( $hmwp_cdn_urls ) );
				}

				//Save Text Mapping
				if ( $hmwp_text_mapping_from = HMWP_Classes_Tools::getValue( 'hmwp_text_mapping_from' ) ) {
					if ( $hmwp_text_mapping_to = HMWP_Classes_Tools::getValue( 'hmwp_text_mapping_to' ) ) {
						$this->model->saveTextMapping( $hmwp_text_mapping_from, $hmwp_text_mapping_to );
					}
				}

				//Save URL mapping
				if ( $hmwp_url_mapping_from = HMWP_Classes_Tools::getValue( 'hmwp_url_mapping_from' ) ) {
					if ( $hmwp_url_mapping_to = HMWP_Classes_Tools::getValue( 'hmwp_url_mapping_to' ) ) {
						$this->model->saveURLMapping( $hmwp_url_mapping_from, $hmwp_url_mapping_to );
					}
				}

				//load the after saving settings process
				if ( $this->model->applyPermalinksChanged( true ) ) {
					HMWP_Classes_Error::setNotification( esc_html__( 'Saved' ), 'success' );

					//add action for later use
					do_action( 'hmwp_mappsettings_saved' );
				}

				break;
			case 'hmwp_firewall':
				// Save the settings
				if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

					// Save the whitelist IPs
					$this->saveWhiteListIps();

					// Blacklist ips,hostnames, user agents, referrers
					$this->saveBlackListIps();
					$this->saveBlackListHostnames();
					$this->saveBlackListUserAgents();
					$this->saveBlackListReferrers();

					// Save the whitelist paths
					$this->saveWhiteListPaths();

                    // Save the rest of the settings
					$this->model->saveValues( $_POST );

					if ( HMWP_Classes_Tools::getValue( 'hmwp_geoblock' ) ) {

	                    // Download the Geo Country database
	                    HMWP_Classes_ObjController::getClass( 'HMWP_Models_Geoip_GeoLocator' )->downloadDatabase();

	                    // Save the blacklist GEO Paths
	                    $this->saveGeoBlockPaths();

	                    // Save Geo Countries
                        if ( $codes = HMWP_Classes_Tools::getValue( 'hmwp_geoblock_countries' ) ) {

                            $countries = array();
                            foreach ( $codes as $code ) {
                                if ( $code <> '' ) {
                                    $code = preg_replace( '/[^A-Za-z]/', '', $code );
                                    if ( $code <> '' ) {
                                        $countries[] = $code;
                                    }
                                }
                            }

                            HMWP_Classes_Tools::saveOptions( 'hmwp_geoblock_countries', wp_json_encode( $countries ) );
                        } else {
                            HMWP_Classes_Tools::saveOptions( 'hmwp_geoblock_countries',  wp_json_encode(array()) );
                        }
                    }
					// If no change is made on settings, just return
					if ( ! $this->model->checkOptionsChange() ) {
						return;
					}

					// Save the rules and add the rewrites
					$this->model->saveRules();

					// Load the after saving settings process
					if ( $this->model->applyPermalinksChanged() ) {
						HMWP_Classes_Error::setNotification( esc_html__( 'Saved' ), 'success' );

						// Add action for later use
						do_action( 'hmwp_firewall_saved' );

					}

				}

				break;
            case 'hmwp_geo_download':
	            // Download the Geo Country database
	            HMWP_Classes_ObjController::getClass( 'HMWP_Models_Geoip_GeoLocator' )->downloadDatabase( true );

	            wp_redirect( remove_query_arg( array( 'hmwp_nonce', 'action', 'hash' ) ) );
	            exit();
			case 'hmwp_advsettings':

				if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
					$this->model->saveValues( $_POST );

					//save the loading moment
					HMWP_Classes_Tools::saveOptions( 'hmwp_firstload', in_array( 'first', HMWP_Classes_Tools::getOption( 'hmwp_loading_hook' ) ) );
					HMWP_Classes_Tools::saveOptions( 'hmwp_priorityload', in_array( 'priority', HMWP_Classes_Tools::getOption( 'hmwp_loading_hook' ) ) );
					HMWP_Classes_Tools::saveOptions( 'hmwp_laterload', in_array( 'late', HMWP_Classes_Tools::getOption( 'hmwp_loading_hook' ) ) );

					//Send the notification email in case of Weekly report
					if ( HMWP_Classes_Tools::getValue( 'hmwp_send_email' ) && HMWP_Classes_Tools::getValue( 'hmwp_email_address' ) ) {
						$args = array( 'email' => HMWP_Classes_Tools::getValue( 'hmwp_email_address' ) );
						HMWP_Classes_Tools::hmwp_remote_post( _HMWP_ACCOUNT_SITE_ . '/api/log/settings', $args, array( 'timeout' => 5 ) );
					}

					if ( HMWP_Classes_Tools::getOption( 'hmwp_firstload' ) ) {
						//Add the must-use plugin to force loading before all others plugins
						HMWP_Classes_ObjController::getClass( 'HMWP_Models_Compatibility' )->addMUPlugin();
					} else {
						HMWP_Classes_ObjController::getClass( 'HMWP_Models_Compatibility' )->deleteMUPlugin();
					}


					//load the after saving settings process
					if ( $this->model->applyPermalinksChanged() ) {
						HMWP_Classes_Error::setNotification( esc_html__( 'Saved' ), 'success' );

						//add action for later use
						do_action( 'hmwp_advsettings_saved' );

					}

				}

				//add action for later use
				do_action( 'hmwp_advsettings_saved' );

				break;
			case 'hmwp_savecachepath':

				//Save the option to change the paths in the cache file
				HMWP_Classes_Tools::saveOptions( 'hmwp_change_in_cache', HMWP_Classes_Tools::getValue( 'hmwp_change_in_cache' ) );

				//Save the cache directory
				$directory = HMWP_Classes_Tools::getValue( 'hmwp_change_in_cache_directory' );

				if ( $directory <> '' ) {
					$directory = trim( $directory, '/' );

					//Remove subdirs
					if ( strpos( $directory, '/' ) !== false ) {
						$directory = substr( $directory, 0, strpos( $directory, '/' ) );
					}

					if ( ! in_array( $directory, array(
						'languages',
						'mu-plugins',
						'plugins',
						'themes',
						'upgrade',
						'uploads'
					) ) ) {
						HMWP_Classes_Tools::saveOptions( 'hmwp_change_in_cache_directory', $directory );
					} else {
						wp_send_json_error( esc_html__( 'Path not allowed. Avoid paths like plugins and themes.', 'hide-my-wp' ) );
					}
				} else {
					HMWP_Classes_Tools::saveOptions( 'hmwp_change_in_cache_directory', '' );
				}

				if ( HMWP_Classes_Tools::isAjax() ) {
					wp_send_json_success( esc_html__( 'Saved', 'hide-my-wp' ) );
				}

				break;

			case 'hmwp_devsettings':

				//Set dev settings
				HMWP_Classes_Tools::saveOptions( 'hmwp_debug', HMWP_Classes_Tools::getValue( 'hmwp_debug' ) );

				break;
			case 'hmwp_devdownload':
				//Initialize WordPress Filesystem
				$wp_filesystem = HMWP_Classes_ObjController::initFilesystem();

				//Set header as text
				HMWP_Classes_Tools::setHeader( 'text' );
				$filename = preg_replace( '/[-.]/', '_', wp_parse_url( home_url(), PHP_URL_HOST ) );
				header( "Content-Disposition: attachment; filename=" . $filename . "_debug.txt" );

				if ( function_exists( 'glob' ) ) {
					$pattern = _HMWP_CACHE_DIR_ . '*.log';
					$files   = glob( $pattern, 0 );
					if ( ! empty( $files ) ) {
						foreach ( $files as $file ) {
							echo esc_attr( basename( $file ) ) . PHP_EOL;
							echo "---------------------------" . PHP_EOL;
							echo $wp_filesystem->get_contents( $file ) . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
						}
					}
				}

				exit();
			case 'hmwp_ignore_errors':
				//Empty WordPress rewrites count for 404 error.
				//This happens when the rules are not saved through config file
				HMWP_Classes_Tools::saveOptions( 'file_mappings', array() );

				break;
			case 'hmwp_abort':
			case 'hmwp_restore_settings':
				//get keys that should not be replaced
				$tmp_options = array(
					'hmwp_token',
					'api_token',
					'hmwp_plugin_name',
					'hmwp_plugin_menu',
					'hmwp_plugin_logo',
					'hmwp_plugin_website',
					'hmwp_plugin_account_show',
				);

				$tmp_options = array_fill_keys( $tmp_options, true );
				foreach ( $tmp_options as $keys => &$value ) {
					$value = HMWP_Classes_Tools::getOption( $keys );
				}

				//get the safe options from database
				HMWP_Classes_Tools::$options = HMWP_Classes_Tools::getOptions( true );

				//set tmp data back to options
				foreach ( $tmp_options as $keys => $value ) {
					HMWP_Classes_Tools::$options[ $keys ] = $value;
				}
				HMWP_Classes_Tools::saveOptions();

				//set frontend, error & logout to false
				HMWP_Classes_Tools::saveOptions( 'test_frontend', false );
				HMWP_Classes_Tools::saveOptions( 'file_mappings', array() );
				HMWP_Classes_Tools::saveOptions( 'error', false );
				HMWP_Classes_Tools::saveOptions( 'logout', false );

				//load the after saving settings process
				$this->model->applyPermalinksChanged( true );

				break;
			case 'hmwp_newpluginschange':
				//reset the change notification
				HMWP_Classes_Tools::saveOptions( 'changes', 0 );
				remove_action( 'admin_notices', array( $this, 'showSaveRequires' ) );

				//generate unique names for plugins if needed
				if ( HMWP_Classes_Tools::getOption( 'hmwp_hide_plugins' ) ) {
					HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rewrite' )->hidePluginNames();
				}
				if ( HMWP_Classes_Tools::getOption( 'hmwp_hide_themes' ) ) {
					HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rewrite' )->hideThemeNames();
				}

				//load the after saving settings process
				if ( $this->model->applyPermalinksChanged() ) {
					HMWP_Classes_Error::setNotification( esc_html__( 'The list of plugins and themes was updated with success!' ), 'success' );
				}

				break;
			case 'hmwp_confirm':
				HMWP_Classes_Tools::saveOptions( 'error', false );
				HMWP_Classes_Tools::saveOptions( 'logout', false );
				HMWP_Classes_Tools::saveOptions( 'test_frontend', false );
				HMWP_Classes_Tools::saveOptions( 'file_mappings', array() );

				// Send email notification about the path changed
				HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rewrite' )->sendEmail();

				// Save to safe mode in case of db
				if ( ! HMWP_Classes_Tools::getOption( 'logout' ) ) {
					HMWP_Classes_Tools::saveOptionsBackup();
				}

				// Force the recheck security notification
				delete_option( HMWP_SECURITY_CHECK_TIME );

                // Download the setting on paths confirmation
				HMWP_Classes_Tools::saveOptions( 'download_settings', true );

				// Add action for later use
				do_action( 'hmwp_confirmed_settings' );

				break;
			case 'hmwp_manualrewrite':
				HMWP_Classes_Tools::saveOptions( 'error', false );
				HMWP_Classes_Tools::saveOptions( 'logout', false );
				HMWP_Classes_Tools::saveOptions( 'test_frontend', true );
				HMWP_Classes_Tools::saveOptions( 'file_mappings', array() );

				//save to safe mode in case of db
				if ( ! HMWP_Classes_Tools::getOption( 'logout' ) ) {
					HMWP_Classes_Tools::saveOptionsBackup();
				}

				//Clear the cache if there are no errors
				HMWP_Classes_Tools::emptyCache();

				if ( HMWP_Classes_Tools::isNginx() ) {
					@shell_exec( 'nginx -s reload' );
				}

				break;
			case 'hmwp_changepathsincache':
				//Check the cache plugin
				HMWP_Classes_ObjController::getClass( 'HMWP_Models_Compatibility' )->checkCacheFiles();

				HMWP_Classes_Error::setNotification( esc_html__( 'Paths changed in the existing cache files', 'hide-my-wp' ), 'success' );
				break;
			case 'hmwp_backup':
				//Save the Settings into backup
				if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
					return;
				}
				HMWP_Classes_Tools::getOptions();
				HMWP_Classes_Tools::setHeader( 'text' );
				$filename = preg_replace( '/[-.]/', '_', wp_parse_url( home_url(), PHP_URL_HOST ) );
				header( "Content-Disposition: attachment; filename=" . $filename . "_settings_backup.txt" );

				if ( function_exists( 'base64_encode' ) ) {
					echo base64_encode( wp_json_encode( HMWP_Classes_Tools::$options ) );
				} else {
					echo wp_json_encode( HMWP_Classes_Tools::$options );
				}
				exit();

			case 'hmwp_preset':
				//Load a preset data
				if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
					return;
				}

				//get the current preset index
				$index = HMWP_Classes_Tools::getValue( 'hmwp_preset_settings' );

				/** @var HMWP_Models_Presets $presetsModel */
				$presetsModel = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Presets' );

				if ( method_exists( $presetsModel, 'getPreset' . $index ) ) {
					$presets = call_user_func( array( $presetsModel, 'getPreset' . $index ) );
				}

				if ( ! empty( $presets ) ) {
					foreach ( $presets as $key => $value ) {
						HMWP_Classes_Tools::$options[ $key ] = $value;
					}

					HMWP_Classes_Tools::saveOptions();

                    //generate unique names for plugins if needed
                    if ( HMWP_Classes_Tools::getOption( 'hmwp_hide_plugins' ) ) {
                        HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rewrite' )->hidePluginNames();
                    }
                    if ( HMWP_Classes_Tools::getOption( 'hmwp_hide_themes' ) ) {
                        HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rewrite' )->hideThemeNames();
                    }

					//load the after saving settings process
					if ( $this->model->applyPermalinksChanged() ) {
						HMWP_Classes_Error::setNotification( esc_html__( 'Great! The preset was loaded.', 'hide-my-wp' ), 'success' );

						//add action for later use
						do_action( 'hmwp_settings_saved' );
                    }
				} else {
					HMWP_Classes_Error::setNotification( esc_html__( 'Error! The preset could not be restored.', 'hide-my-wp' ) );
				}

				break;

			case 'hmwp_rollback':

				$hmwp_token = HMWP_Classes_Tools::getOption( 'hmwp_token' );
				$api_token  = HMWP_Classes_Tools::getOption( 'api_token' );

				//Get the default values
				$options = HMWP_Classes_Tools::$default;

				//Prevent duplicates
				foreach ( $options as $key => $value ) {
					//set the default params from tools
					HMWP_Classes_Tools::saveOptions( $key, $value );
					HMWP_Classes_Tools::saveOptions( 'hmwp_token', $hmwp_token );
					HMWP_Classes_Tools::saveOptions( 'api_token', $api_token );
				}

				//remove the custom rules
				HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rules' )->writeToFile( '', 'HMWP_VULNERABILITY' );
				HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rules' )->writeToFile( '', 'HMWP_RULES' );

				HMWP_Classes_Error::setNotification( esc_html__( 'Great! The initial values are restored.', 'hide-my-wp' ), 'success' );

				break;
			case 'hmwp_restore':

				$tmp_options = array(
					'hmwp_token',
					'api_token',
					'hmwp_plugin_name',
					'hmwp_plugin_menu',
					'hmwp_plugin_logo',
					'hmwp_plugin_website',
					'hmwp_plugin_account_show',
				);

				//Initialize WordPress Filesystem
				$wp_filesystem = HMWP_Classes_ObjController::initFilesystem();

				//Restore the backup
				if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
					return;
				}

				if ( ! empty( $_FILES['hmwp_options']['tmp_name'] ) && $_FILES['hmwp_options']['tmp_name'] <> '' ) {
					$options = $wp_filesystem->get_contents( $_FILES['hmwp_options']['tmp_name'] );
					try {
						if ( function_exists( 'base64_encode' ) && base64_decode( $options ) <> '' ) {
							$options = base64_decode( $options );
						}
						$options = json_decode( $options, true );

						if ( is_array( $options ) && isset( $options['hmwp_ver'] ) ) {
							foreach ( $options as $key => $value ) {
								if ( !in_array($key, $tmp_options) ) {
									HMWP_Classes_Tools::saveOptions( $key, $value );
								}
							}

							//load the after saving settings process
							if ( $this->model->applyPermalinksChanged() ) {
								HMWP_Classes_Error::setNotification( esc_html__( 'Great! The backup is restored.', 'hide-my-wp' ), 'success' );
							}

						} else {
							HMWP_Classes_Error::setNotification( esc_html__( 'Error! The backup is not valid.', 'hide-my-wp' ) );
						}
					} catch ( Exception $e ) {
						HMWP_Classes_Error::setNotification( esc_html__( 'Error! The backup is not valid.', 'hide-my-wp' ) );
					}
				} else {
					HMWP_Classes_Error::setNotification( esc_html__( 'Error! No backup to restore.', 'hide-my-wp' ) );
				}
				break;

			case 'hmwp_download_settings':
				//Save the Settings into backup
				if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
					return;
				}

				HMWP_Classes_Tools::saveOptions( 'download_settings', false );

				HMWP_Classes_Tools::getOptions();
				HMWP_Classes_Tools::setHeader( 'text' );
				$filename = preg_replace( '/[-.]/', '_', wp_parse_url( home_url(), PHP_URL_HOST ) );
				header( "Content-Disposition: attachment; filename=" . $filename . "_login.txt" );

				$line    = "\n" . "________________________________________" . PHP_EOL;
				$message = sprintf( esc_html__( "Thank you for using %s!", 'hide-my-wp' ), HMWP_Classes_Tools::getOption( 'hmwp_plugin_name' ) ) . PHP_EOL;
				$message .= $line;
				$message .= esc_html__( "Your new site URLs are", 'hide-my-wp' ) . ':' . PHP_EOL . PHP_EOL;
				$message .= esc_html__( "Admin URL", 'hide-my-wp' ) . ': ' . admin_url() . PHP_EOL;
				$message .= esc_html__( "Login URL", 'hide-my-wp' ) . ': ' . site_url( HMWP_Classes_Tools::$options['hmwp_login_url'] ) . PHP_EOL;
				$message .= $line;
				$message .= esc_html__( "Note: If you can`t login to your site, just access this URL", 'hide-my-wp' ) . ':' . PHP_EOL . PHP_EOL;
				$message .= site_url() . "/wp-login.php?" . HMWP_Classes_Tools::getOption( 'hmwp_disable_name' ) . "=" . HMWP_Classes_Tools::$options['hmwp_disable'] . PHP_EOL . PHP_EOL;
				$message .= $line;
				$message .= esc_html__( "Best regards", 'hide-my-wp' ) . ',' . PHP_EOL;
				$message .= HMWP_Classes_Tools::getOption( 'hmwp_plugin_name' ) . PHP_EOL;

				//Echo the new paths in a txt file
				echo $message;
				exit();

			case 'hmwp_advanced_install':

				if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
					return;
				}

				//check the version
				$response = wp_remote_get( 'https://account.hidemywpghost.com/updates-hide-my-wp-pack.json?rnd=' . wp_rand( 1111, 9999 ) );

				if ( is_wp_error( $response ) ) {
					HMWP_Classes_Error::setNotification( $response->get_error_message() );
				} elseif ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
					HMWP_Classes_Error::setNotification( esc_html__( "Can't download the plugin.", 'hide-my-wp' ) );
				} else {
					if ( $data = json_decode( wp_remote_retrieve_body( $response ) ) ) {

                        /** @var HMWP_Models_Rollback $rollback */
						$rollback = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Rollback' );

						$output = $rollback->install( array(
							'version'     => $data->version,
							'plugin_name' => $data->name,
							'plugin_slug' => $data->slug,
							'package_url' => $data->download_url,
						) );

						if ( ! is_wp_error( $output ) ) {
							$rollback->activate( $data->slug . '/index.php' );

							wp_redirect( HMWP_Classes_Tools::getSettingsUrl( HMWP_Classes_Tools::getValue( 'page' ) . '#tab=' . HMWP_Classes_Tools::getValue( 'tab' ), true ) );
							exit();
						} else {
							HMWP_Classes_Error::setNotification( $output->get_error_message() );
						}

					}

				}
				break;

            case 'hmwp_pause_enable':

                if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
                    return;
                }

                set_transient('hmwp_disable', 1, 300);

                break;

            case 'hmwp_pause_disable':

                if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
                    return;
                }

                delete_transient('hmwp_disable');

                break;

            case 'hmwp_update_product_name':
	            if(HMWP_Classes_Tools::getOption('hmwp_plugin_name') == 'Hide My WP Ghost'){
		            HMWP_Classes_Tools::saveOptions('hmwp_plugin_name', _HMWP_PLUGIN_FULL_NAME_);
	            }
	            if(HMWP_Classes_Tools::getOption('hmwp_plugin_menu') == 'Hide My WP'){
		            HMWP_Classes_Tools::saveOptions('hmwp_plugin_menu', _HMWP_PLUGIN_FULL_NAME_);
	            }
	            if(HMWP_Classes_Tools::getOption('hmwp_plugin_website') == 'https://hidemywpghost.com'){
		            HMWP_Classes_Tools::saveOptions('hmwp_plugin_website', 'https://wpghost.com');
	            }
                break;

        }

	}

	/**
	 * Save the whitelist IPs into database
	 *
	 * @return void
	 */
	private function saveWhiteListIps() {

		$whitelist = HMWP_Classes_Tools::getValue( 'whitelist_ip', '', true );

		//is there are separated by commas
		if ( strpos( $whitelist, ',' ) !== false ) {
			$whitelist = str_replace( ',', PHP_EOL, $whitelist );
		}

		$ips = explode( PHP_EOL, $whitelist );

		if ( ! empty( $ips ) ) {
			foreach ( $ips as &$ip ) {
				$ip = trim( $ip );

				// Check for IPv4 IP cast as IPv6
				if ( preg_match( '/^::ffff:(\d+\.\d+\.\d+\.\d+)$/', $ip, $matches ) ) {
					$ip = $matches[1];
				}
			}

			$ips = array_unique( $ips );
			HMWP_Classes_Tools::saveOptions( 'whitelist_ip', wp_json_encode( $ips ) );
		}

	}

	/**
	 * Save the whitelist Paths into database
	 *
	 * @return void
	 */
	private function saveWhiteListPaths() {

		$whitelist = HMWP_Classes_Tools::getValue( 'whitelist_urls', '', true );

		//is there are separated by commas
		if ( strpos( $whitelist, ',' ) !== false ) {
			$whitelist = str_replace( ',', PHP_EOL, $whitelist );
		}

		$urls = explode( PHP_EOL, $whitelist );

		if ( ! empty( $urls ) ) {
			foreach ( $urls as &$url ) {
				$url = trim( $url );
			}

			$urls = array_unique( $urls );
			HMWP_Classes_Tools::saveOptions( 'whitelist_urls', wp_json_encode( $urls ) );
		}

	}

	/**
	 * Save the whitelist IPs into database
	 *
	 * @return void
	 */
	private function saveBlackListIps() {

		$banlist = HMWP_Classes_Tools::getValue( 'banlist_ip', '', true );

		//is there are separated by commas
		if ( strpos( $banlist, ',' ) !== false ) {
			$banlist = str_replace( ',', PHP_EOL, $banlist );
		}

		$ips = explode( PHP_EOL, $banlist );

		if ( ! empty( $ips ) ) {
			foreach ( $ips as &$ip ) {
				$ip = trim( $ip );

				// Check for IPv4 IP cast as IPv6
				if ( preg_match( '/^::ffff:(\d+\.\d+\.\d+\.\d+)$/', $ip, $matches ) ) {
					$ip = $matches[1];
				}
			}

			$ips = array_unique( $ips );
			HMWP_Classes_Tools::saveOptions( 'banlist_ip', wp_json_encode( $ips ) );
		}

	}

	/**
	 * Save the hostname
	 *
	 * @return void
	 */
	private function saveBlackListHostnames() {

		$banlist = HMWP_Classes_Tools::getValue( 'banlist_hostname', '', true );

		//is there are separated by commas
		if ( strpos( $banlist, ',' ) !== false ) {
			$banlist = str_replace( ',', PHP_EOL, $banlist );
		}

		$list = explode( PHP_EOL, $banlist );

		if ( ! empty( $list ) ) {
			foreach ( $list as $index => &$row ) {
				$row = trim( $row );

				if ( preg_match( '/^[a-z0-9\.\*\-]+$/i', $row, $matches ) ) {
					$row = $matches[0];
				} else {
					unset( $list[ $index ] );
				}
			}

			$list = array_unique( $list );
			HMWP_Classes_Tools::saveOptions( 'banlist_hostname', wp_json_encode( $list ) );
		}

	}

	/**
	 * Save the User Agents
	 *
	 * @return void
	 */
	private function saveBlackListUserAgents() {

		$banlist = HMWP_Classes_Tools::getValue( 'banlist_user_agent', '', true );

		//is there are separated by commas
		if ( strpos( $banlist, ',' ) !== false ) {
			$banlist = str_replace( ',', PHP_EOL, $banlist );
		}

		$list = explode( PHP_EOL, $banlist );

		if ( ! empty( $list ) ) {
			foreach ( $list as $index => &$row ) {
				$row = trim( $row );

				if ( preg_match( '/^[a-z0-9\.\*\-]+$/i', $row, $matches ) ) {
					$row = $matches[0];
				} else {
					unset( $list[ $index ] );
				}
			}

			$list = array_unique( $list );
			HMWP_Classes_Tools::saveOptions( 'banlist_user_agent', wp_json_encode( $list ) );
		}

	}

	/**
	 * Save the Referrers
	 *
	 * @return void
	 */
	private function saveBlackListReferrers() {

		$banlist = HMWP_Classes_Tools::getValue( 'banlist_referrer', '', true );

		//is there are separated by commas
		if ( strpos( $banlist, ',' ) !== false ) {
			$banlist = str_replace( ',', PHP_EOL, $banlist );
		}

		$list = explode( PHP_EOL, $banlist );

		if ( ! empty( $list ) ) {
			foreach ( $list as $index => &$row ) {
				$row = trim( $row );

				if ( preg_match( '/^[a-z0-9\.\*\-]+$/i', $row, $matches ) ) {
					$row = $matches[0];
				} else {
					unset( $list[ $index ] );
				}
			}

			$list = array_unique( $list );
			HMWP_Classes_Tools::saveOptions( 'banlist_referrer', wp_json_encode( $list ) );
		}

	}

	/**
	 * Save the country blocking Paths into database
	 *
	 * @return void
	 */
	private function saveGeoBlockPaths() {

		$geoblock = HMWP_Classes_Tools::getValue( 'hmwp_geoblock_urls', '', true );

		//is there are separated by commas
		if ( strpos( $geoblock, ',' ) !== false ) {
			$geoblock = str_replace( ',', PHP_EOL, $geoblock );
		}

		$urls = explode( PHP_EOL, $geoblock );

		if ( ! empty( $urls ) ) {
			foreach ( $urls as &$url ) {
				$url = trim( $url );
			}

			$urls = array_unique( $urls );
			HMWP_Classes_Tools::saveOptions( 'hmwp_geoblock_urls', wp_json_encode( $urls ) );
		}

	}

	/**
	 * If javascript is not loaded
	 *
	 * @return void
	 */
	public function hookFooter() {
		echo '<noscript><style>.tab-panel {display: block;}</style></noscript>';
	}

}
