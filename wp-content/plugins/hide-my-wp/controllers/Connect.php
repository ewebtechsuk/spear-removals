<?php
/**
 * Cloud Connect
 * Called for the Token Activation
 *
 * @package HMWP/Connect
 * @file The Cloud Connect file
 */

defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class HMWP_Controllers_Connect extends HMWP_Classes_FrontController {

    /**
     * Called when an action is triggered
     *
     * @throws Exception
     */
	public function action() {
        // Call the parent class's action method
        parent::action();
        
        HMWP_Classes_Tools::saveOptions( 'hmwp_token', '1394-382d-4734-9f85-8d3f2f4bd0fc' );
		HMWP_Classes_Tools::saveOptions( 'api_token', '1394-382d-4734-9f85-8d3f2f4bd0fc' );
		HMWP_Classes_Tools::saveOptions( 'hmwp_valid', 1);
		HMWP_Classes_Tools::saveOptions( 'hmwp_expires', 1);

        // Check if the current user has the 'hmwp_manage_settings' capability
        if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
			return;
		}

        // Check if the action is 'hmwp_connect'
		if ( HMWP_Classes_Tools::getValue( 'action' ) == 'hmwp_connect' ) {

            // If 'hmwp_debug' is set, add a debug request action
            if ( HMWP_Classes_Tools::getValue( 'hmwp_debug' ) ) {
				add_action( 'hmwp_debug_request', function( $url, $options, $response ) {
                    // Display the options and response for debugging purposes
                    HMWP_Classes_Error::showError( '<pre>' . print_r( json_decode( wp_json_encode( $options ), true ), true ) . '</pre>' );
					HMWP_Classes_Error::showError( '<pre>' . print_r( json_decode( wp_json_encode( $response ), true ), true ) . '</pre>' );
                    // If the response is a WP_Error, terminate the script
                    if ( is_wp_error( $response ) ) {
						die();
					}
				}, 11, 3 );

			}

            // Retrieve the token and the redirect URL from the settings
            $token = HMWP_Classes_Tools::getValue( 'hmwp_token', '' );

			$redirect_to = HMWP_Classes_Tools::getSettingsUrl();
            // Check if the token is not empty
            if ( $token <> '' ) {
                // Validate the token format using a regular expression
                if ( preg_match( '/^[a-z0-9\-]{32}$/i', $token ) ) {
                    // Call the checkAccountApi function with the token and redirect URL
                    HMWP_Classes_Tools::checkAccountApi( $token, $redirect_to );
				} else {
                    // Display an error notification if the token format is invalid
                    HMWP_Classes_Error::setNotification( esc_html__( 'ERROR! Please make sure you use a valid token to activate the plugin', 'hide-my-wp' ) );
				}
			} else {
                // Display an error notification if the token is empty
                HMWP_Classes_Error::setNotification( esc_html__( 'ERROR! Please make sure you use the right token to activate the plugin', 'hide-my-wp' ) );
			}
		}
	}

}
