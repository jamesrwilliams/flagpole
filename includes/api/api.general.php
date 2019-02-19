<?php
/**
 * Theme API endpoints.
 *
 * These are all the functions that people can
 * use directly within their theme code.
 *
 * @package wp-feature-flags
 */

use FeatureFlags\FeatureFlags;

/**
 * Register a feature flag with the plugin.
 *
 * @param array $args Settings and options for each flag.
 * @return void
 */
function register_feature_flag( $args ) {

	$defaults = [

		'enforced'    => false,
		'description' => '',
		'stable'      => false,

	];

	if ( isset( $args[0] ) && is_array( $args[0] ) ) {

		foreach ( $args as $declaration ) {

			$args = wp_parse_args( $declaration, $defaults );

			if ( isset( $args['title'] ) && isset( $args['key'] ) ) {

				FeatureFlags::init()->add_flag( $args );

			} else {

				add_action(
					'admin_notices',
					function() {

						$class   = 'notice notice-error';
						$message = 'Malformed featureFlag - Need to supply a key and a title.';

						printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );

					}
				);
			}
		}
	} else {

		$args = wp_parse_args( $args, $defaults );

		if ( isset( $args['title'] ) && isset( $args['key'] ) ) {

			FeatureFlags::init()->add_flag( $args );

		} else {

			add_action(
				'admin_notices',
				function() {

					$class   = 'notice notice-error';
					$message = 'Malformed featureFlag - Need to supply a key and a title.';

					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );

				}
			);
		}
	}
}

/**
 * Check if a user has enabled a flag.
 *
 * @param string $flag_key The key for the flag we're after.
 * @return mixed
 */
function has_user_enabled( $flag_key = '' ) {

	return FeatureFlags::init()->has_user_enabled_flag( $flag_key );

}

/**
 * Check if a user has enabled a flag via a group.
 *
 * @param string $flag_key The key of the flag we're checking.
 *
 * @return bool
 */
function has_user_enabled_via_group( $flag_key = '' ) {
	return FeatureFlags::init()->user_enabled_key_via_group( $flag_key );
}

if ( ! function_exists( 'is_enabled' ) ) {

	/**
	 * Check if a a flag is enabled.
	 *
	 * @param string $flag_key The key for the flag we're after.
	 * @return bool
	 */
	function is_enabled( $flag_key = '' ) {
		return FeatureFlags::init()->is_enabled( $flag_key );
	}
}
