<?php
/**
 * Theme API endpoints.
 *
 * @package feature-flags
 */

use FeatureFlags\FeatureFlags;

/**
 * Register a feature flag with the plugin.
 *
 * @param array $args Settings and options for each flag.
 *
 * @return void
 */
function register_feature_flag( $args ) {

	$defaults = [

		'enforced'    => false,
		'description' => '',
		'queryable'   => false,

	];

	$args = wp_parse_args( $args, $defaults );

	if ( isset( $args['title'] ) && isset( $args['key'] ) ) {

		FeatureFlags::init()->add_flag( $args );

	} else {

		add_action( 'admin_notices', function() {

			$class   = 'notice notice-error';
			$message = 'Malformed featureFlag - Need to supply a key and a title.';

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );

		} );

	}

}

/**
 * Check if a user has enabled a flag.
 *
 * @param string $feature_key The key for the flag we're after.
 *
 * @return mixed
 */
function has_user_enabled( $feature_key = '' ) {

	return FeatureFlags::init()->has_user_enabled( $feature_key );

}

/**
 * Check if a a flag is enabled.
 *
 * @param string $feature_key The key for the flag we're after.
 *
 * @return bool
 */

if( ! function_exists( 'is_enabled' ) ) {

	function is_enabled( $feature_key = '' ) {

		return FeatureFlags::init()->is_enabled( $feature_key );

	}

}
