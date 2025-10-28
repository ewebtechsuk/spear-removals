<?php
/**
 * Preset Settings in HMWP
 *
 * @file  Preset Settings file
 * @package HMWP/Presets
 * @since 8.0.0
 */
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class HMWP_Models_Presets {

	protected $preset = array();
	protected $current = 1;

	/**
	 * Get defined presets
	 *
	 * @return array
	 */
	public function getPresetsSelect() {
		return array(
			1 => __( "Minimal (No Config Rewrites)", 'hide-my-wp' ),
			2 => __( "Safe Mode + Firewall + Compatibility Settings", 'hide-my-wp' ),
			3 => __( "Safe Mode + Firewall + Brute Force + Events Log + Two factor", 'hide-my-wp' ),
			4 => __( "Ghost Mode + Firewall + Brute Force + Events Log + Two factor", 'hide-my-wp' ),
		);
	}

	/**
	 * Get the title for the current option
	 *
	 * @param string $name Option name
	 *
	 * @return string|false
	 */
	public function getPresetTitles( $name ) {
		switch ( $name ) {
			case 'hmwp_admin_url':
				return __( 'Custom Admin Path', 'hide-my-wp' );
			case 'hmwp_hide_admin':
				return __( 'Hide "wp-admin"', 'hide-my-wp' );
			case 'hmwp_login_url':
				return __( 'Custom Login Path', 'hide-my-wp' );
			case 'hmwp_hide_login':
				return __( 'Hide "login" Path', 'hide-my-wp' );
			case 'hmwp_hide_newlogin':
				return __( 'Hide the New Login Path', 'hide-my-wp' );
			case 'hmwp_admin-ajax_url':
				return __( 'Custom admin-ajax Path', 'hide-my-wp' );
			case 'hmwp_hideajax_admin':
				return __( 'Hide wp-admin from Ajax URL', 'hide-my-wp' );
			case 'hmwp_hideajax_paths':
				return __( 'Change Paths in Ajax Calls', 'hide-my-wp' );
			case 'hmwp_wp-content_url':
				return __( 'Custom wp-content Path', 'hide-my-wp' );
			case 'hmwp_wp-includes_url':
				return __( 'Custom wp-includes Path', 'hide-my-wp' );
			case 'hmwp_upload_url':
				return __( 'Custom uploads Path', 'hide-my-wp' );
			case 'hmwp_author_url':
				return __( 'Custom author Path', 'hide-my-wp' );
			case 'hmwp_hide_authors':
				return __( 'Hide Author ID URL', 'hide-my-wp' );
			case 'hmwp_plugin_url':
				return __( 'Custom plugins Path', 'hide-my-wp' );
			case 'hmwp_hide_plugins':
				return __( 'Hide Plugin Names', 'hide-my-wp' );
			case 'hmwp_themes_url':
				return __( 'Custom themes Path', 'hide-my-wp' );
			case 'hmwp_hide_themes':
				return __( 'Hide Theme Names', 'hide-my-wp' );
			case 'hmwp_themes_style':
				return __( 'Custom theme style name', 'hide-my-wp' );
			case 'hmwp_wp-comments-post':
				return __( 'Custom comment Path', 'hide-my-wp' );
			case 'hmwp_hide_oldpaths':
				return __( 'Hide WordPress Common Paths', 'hide-my-wp' );
			case 'hmwp_hide_commonfiles':
				return __( 'Hide WordPress Common Files', 'hide-my-wp' );
			///////////////////////////////////////////////////////////////
			case 'hmwp_sqlinjection':
				return __( 'Firewall Against Script Injection', 'hide-my-wp' );
			case 'hmwp_sqlinjection_level':
				return __( 'Firewall Strength', 'hide-my-wp' );
			case 'hmwp_hide_unsafe_headers':
				return __( 'Remove Unsafe Headers', 'hide-my-wp' );
			case 'hmwp_detectors_block':
				return __( 'Block Theme Detectors Crawlers', 'hide-my-wp' );
			case 'hmwp_security_header':
				return __( 'Add Security Headers for XSS and Code Injection Attacks', 'hide-my-wp' );
			case 'hmwp_hide_version':
				return __( 'Hide Version from Images, CSS and JS in WordPress', 'hide-my-wp' );
			case 'hmwp_hide_version_random':
				return __( 'Random Static Number', 'hide-my-wp' );
			case 'hmwp_hide_styleids':
				return __( 'Hide IDs from META Tags', 'hide-my-wp' );
			case 'hmwp_hide_prefetch':
				return __( 'Hide WordPress DNS Prefetch META Tags', 'hide-my-wp' );
			case 'hmwp_hide_generator':
				return __( 'Hide WordPress Generator META Tags', 'hide-my-wp' );
			case 'hmwp_hide_comments':
				return __( 'Hide HTML Comments', 'hide-my-wp' );
			case 'hmwp_disable_embeds':
				return __( 'Hide Embed scripts', 'hide-my-wp' );
			case 'hmwp_disable_manifest':
				return __( 'Hide WLW Manifest scripts', 'hide-my-wp' );
			case 'hmwp_mapping_text_show':
				return __( 'Text Mapping', 'hide-my-wp' );
			case 'hmwp_mapping_url_show':
				return __( 'URL Mapping', 'hide-my-wp' );
			case 'hmwp_bruteforce':
				return __( 'Use Brute Force Protection', 'hide-my-wp' );
			case 'hmwp_bruteforce_username':
				return __( 'Wrong Username Protection', 'hide-my-wp' );
			case 'hmwp_activity_log':
				return __( 'Log Users Events', 'hide-my-wp' );
		}

		return false;

	}

	/**
	 * Set the current preset
	 *
	 * @param int $index
	 *
	 * @return void
	 */
	public function setCurrentPreset( $index ) {
		$this->current = $index;
	}

	/**
	 * Get the preset data
	 *
	 * @param string $name Preset name
	 *
	 * @return array|false
	 */
	public function getPresetData() {

		if ( method_exists( $this, 'getPreset' . $this->current ) ) {
			$presets = call_user_func( array( $this, 'getPreset' . $this->current ) );

			if ( ! empty( $presets ) ) {
				foreach ( $presets as $name => $value ) {
					if ( $this->getPresetTitles( $name ) ) {
						$this->preset[ $this->current ][ $name ] = array(
							'title' => $this->getPresetTitles( $name ), 'value' => $value
						);
					}
				}

				if ( isset( $this->preset[ $this->current ] ) ) {
					return $this->preset[ $this->current ];
				}
			}
		}


		return false;
	}

	/**
	 * Get firewall option values
	 *
	 * @param $value
	 *
	 * @return string|void
	 */
	public function getFirewallLevel( $value ) {
		switch ( $value ) {
			case 1:
				return esc_html__( 'Minimal', 'hide-my-wp' );
			case 2:
				return esc_html__( 'Medium', 'hide-my-wp' );
			case 3:
				return esc_html__( '7G Firewall', 'hide-my-wp' );
			case 4:
				return esc_html__( '8G Firewall', 'hide-my-wp' );
		}
	}

	/**
	 * Get preset value
	 *
	 * @param $name
	 *
	 * @return mixed|string
	 */
	public function getPresetValue( $name ) {

		$values = $this->getPresetData();

		if ( isset( $values[ $name ]['value'] ) ) {

			$value = $values[ $name ]['value'];

			switch ( $name ) {
				case 'hmwp_sqlinjection_level':
					return $this->getFirewallLevel( $value );
				default:
					if ( is_numeric( $value ) ) {
						return ( $value ? '<span style="color:green">' . esc_html__( 'Yes' ) . '</span>' : '<span style="color:lightgrey">' . esc_html__( 'No' ) . '</span>' );
					} else {
						return $value;
					}
			}

		}

		return false;
	}

	/**
	 * Define preset
	 *
	 * @return array
	 */
	public function getPreset1() {
		$default = HMWP_Classes_Tools::$default;
		$presets = array(
			'hmwp_mode'           => 'lite',
			'hmwp_login_url'           => 'newlogin', 'hmwp_sqlinjection' => 1, 'hmwp_sqlinjection_level' => 2,
			'hmwp_hide_unsafe_headers' => 0, 'hmwp_detectors_block' => 0, 'hmwp_security_header' => 0,
			'hmwp_hide_version'        => 1, 'hmwp_hide_version_random' => 1, 'hmwp_hide_styleids' => 0,
			'hmwp_hide_prefetch'       => 1, 'hmwp_hide_generator' => 1, 'hmwp_hide_comments' => 1,
			'hmwp_disable_embeds'      => 1, 'hmwp_disable_manifest' => 1, 'hmwp_mapping_text_show' => 0,
			'hmwp_mapping_url_show'    => 0, 'hmwp_bruteforce' => 1, 'hmwp_bruteforce_username' => 1,
			'hmwp_activity_log'        => 1,
		);

		return array_merge( $default, $presets );
	}

	/**
	 * Define preset
	 *
	 * @return array
	 */
	public function getPreset2() {
		$default = HMWP_Classes_Tools::$default;
		$lite    = @array_merge( $default, HMWP_Classes_Tools::$lite );
		$presets = array(
			'hmwp_sqlinjection'        => 1, 'hmwp_sqlinjection_level' => 2, 'hmwp_hide_unsafe_headers' => 1,
			'hmwp_detectors_block'     => 1, 'hmwp_security_header' => 1, 'hmwp_hide_version' => 1,
			'hmwp_hide_version_random' => 1, 'hmwp_hide_styleids' => 0, 'hmwp_hide_prefetch' => 1,
			'hmwp_hide_generator'      => 1, 'hmwp_hide_comments' => 0, 'hmwp_disable_embeds' => 0,
			'hmwp_disable_manifest'    => 1, 'hmwp_mapping_text_show' => 0, 'hmwp_mapping_url_show' => 0,
			'hmwp_bruteforce'          => 0, 'hmwp_bruteforce_username' => 0, 'hmwp_activity_log' => 1,
		);

		return array_merge( $lite, $presets );
	}

	/**
	 * Define preset
	 *
	 * @return array
	 */
	public function getPreset3() {
		$default = HMWP_Classes_Tools::$default;
		$lite    = @array_merge( $default, HMWP_Classes_Tools::$lite );
		$presets = array(
			'hmwp_sqlinjection'        => 1, 'hmwp_sqlinjection_level' => 2, 'hmwp_hide_unsafe_headers' => 1,
			'hmwp_detectors_block'     => 1, 'hmwp_security_header' => 1, 'hmwp_hide_version' => 1,
			'hmwp_hide_version_random' => 1, 'hmwp_hide_styleids' => 0, 'hmwp_hide_prefetch' => 1,
			'hmwp_hide_generator'      => 1, 'hmwp_hide_comments' => 1, 'hmwp_disable_embeds' => 1,
			'hmwp_disable_manifest'    => 1, 'hmwp_mapping_text_show' => 1, 'hmwp_mapping_url_show' => 1,
			'hmwp_bruteforce'          => 1, 'hmwp_bruteforce_lostpassword' => 1, 'hmwp_bruteforce_register' => 1, 'hmwp_bruteforce_comments' => 1,
			'hmwp_bruteforce_username' => 1, 'hmwp_activity_log' => 1, 'add_action' => 1,
		);

		return array_merge( $lite, $presets );
	}

	/**
	 * Define preset
	 *
	 * @return array
	 */
	public function getPreset4() {
		$default = HMWP_Classes_Tools::$default;
		$ninja   = @array_merge( $default, HMWP_Classes_Tools::$ninja );
		$presets = array(
			'hmwp_hideajax_paths'         => 1, 'hmwp_disable_rest_api_param' => 1, 'hmwp_hide_oldpaths' => 1,
			'hmwp_hide_oldpaths_plugins'  => 1, 'hmwp_hide_oldpaths_themes' => 1, 'hmwp_themes_style' => 'design.css',
			'hmwp_hide_oldpaths_types'    => array( 'php', 'txt', 'html', 'lock', 'json', 'media' ),
			'hmwp_hide_commonfiles'       => 1, 'hmwp_hide_commonfiles_files' => array(
				'wp-comments-post.php', 'wp-config-sample.php', 'readme.html', 'readme.txt', 'install.php',
				'license.txt', 'php.ini', 'upgrade.php', 'bb-config.php', 'error_log', 'debug.log', 'hidemywp.conf'
			), 'hmwp_disable_browsing'    => 1, 'hmwp_sqlinjection' => 1, 'hmwp_sqlinjection_level' => 4,
			'hmwp_hide_unsafe_headers'    => 1, 'hmwp_detectors_block' => 1, 'hmwp_security_header' => 1,
			'hmwp_hide_in_sitemap'        => 1, 'hmwp_hide_author_in_sitemap' => 1, 'hmwp_robots' => 1,
			'hmwp_hide_loggedusers'       => 1, 'hmwp_fix_relative' => 1, 'hmwp_hide_version' => 1,
			'hmwp_hide_version_random'    => 1, 'hmwp_hide_styleids' => 0, 'hmwp_hide_prefetch' => 1,
			'hmwp_hide_generator'         => 1, 'hmwp_hide_comments' => 1, 'hmwp_disable_embeds' => 1,
			'hmwp_disable_manifest'       => 1, 'hmwp_mapping_text_show' => 1, 'hmwp_mapping_url_show' => 1,
			'hmwp_bruteforce'             => 1, 'hmwp_bruteforce_lostpassword' => 1, 'hmwp_bruteforce_register' => 1, 'hmwp_bruteforce_comments' => 1,
			'hmwp_bruteforce_username'    => 1, 'hmwp_activity_log' => 1, 'hmwp_2falogin' => 1,
		);

		return array_merge( $ninja, $presets );
	}
}
