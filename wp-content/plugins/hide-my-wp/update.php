<?php

namespace YahnisElsts\PluginUpdateChecker\v5p5;

use WP_Error;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory as MajorFactory;
use YahnisElsts\PluginUpdateChecker\v5p5\PucFactory as MinorFactory;

require __DIR__ . '/update/v5p5/Autoloader.php';
new Autoloader();

require __DIR__ . '/update/v5p5/PucFactory.php';
require __DIR__ . '/update/v5/PucFactory.php';

//Register classes defined in this version with the factory.
foreach (
	array(
		'Plugin\\UpdateChecker' => Plugin\UpdateChecker::class,
		'Theme\\UpdateChecker'  => Theme\UpdateChecker::class,

		'Vcs\\PluginUpdateChecker' => Vcs\PluginUpdateChecker::class,
		'Vcs\\ThemeUpdateChecker'  => Vcs\ThemeUpdateChecker::class,

		'GitHubApi'    => Vcs\GitHubApi::class,
		'BitBucketApi' => Vcs\BitBucketApi::class,
		'GitLabApi'    => Vcs\GitLabApi::class,
	)
	as $pucGeneralClass => $pucVersionedClass
) {
	MajorFactory::addVersion($pucGeneralClass, $pucVersionedClass, '5.5');
	//Also add it to the minor-version factory in case the major-version factory
	//was already defined by another, older version of the update checker.
	MinorFactory::addVersion($pucGeneralClass, $pucVersionedClass, '5.5');
}

// Build the update verification
PucFactory::buildUpdateChecker(
    _HMWP_ACCOUNT_SITE_ . '/api/wp/update/',
    _HMWP_ROOT_DIR_ . '/index.php',
    'hide-my-wp' );

// Show the error from API instead of metadata error
add_filter('puc_request_metadata_http_result-hide-my-wp', function ($result, $url, $options){
	if ( ! is_wp_error( $result ) && is_array( $result ) ) {
		if (isset($result['body'])){
			$body = json_decode($result['body'], true);
			if (isset($body['error']) && $body['error'] == 'invalid_request'){
				return new WP_Error( esc_html__( "Activate the plugin first.", "hide-my-wp" ) );
			}
		}
	}

	return $result;
}, 11, 3);