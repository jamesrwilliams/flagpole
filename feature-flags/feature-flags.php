<?php
/**
 * Feature Flags for WordPress.
 * Include things in your WordPress theme using Feature Flags.
 *
 * @package   feature-flags
 * @author    James Williams <james@jamesrwilliams.co.uk>
 * @link      https://jamesrwilliams.co.uk/
 * @copyright 2018 James Williams
 *
 * @wordpress-plugin
 * Plugin Name:       Feature Flags
 * Description:       Easily register and work with feature flags in your theme.
 * Version:           1.0.0
 * Author:            James Williams
 * Text Domain:       featureFlags
 * Author URI:        https://jamesrwilliams.co.uk/
 */

// If this file is called directly abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

use FeatureFlags\FeatureFlags;

// Define plugin paths and url for global usage.
define( 'FF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'FM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FF_VERSION', '1.0.0' );

// Register admin page.
add_action( 'admin_init', function () {

	register_setting( 'ff-settings-page', 'ff_client_secret', function ( $posted_data ) {
		if ( ! $posted_data ) {
			add_settings_error( 'ff_client_secret', 'ff_updated', 'Error Message', 'error' );

			return false;
		}

		return $posted_data;

	} );

} );

/**
 * Plugin styles and scripts.
 *
 * @param string $hook The Hook string.
 */
function feature_flags_admin_imports( $hook ) {

	if ( 'tools_page_feature-flags' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'feature-flags-styles', plugins_url( '/assets/css/feature-flags.css', __FILE__ ), FF_VERSION );
	wp_register_script( 'feature-flags-script', plugins_url( '/assets/js/feature-flags.js', __FILE__ ), FF_VERSION );

	$params = [
		'ajax_nonce' => wp_create_nonce( 'featureFlagNonce' ),
	];

	wp_localize_script( 'feature-flags-script', 'ffwp', $params );

	wp_enqueue_script( 'feature-flags-script' );

}

add_action( 'admin_enqueue_scripts', 'feature_flags_admin_imports' );

// Includes.
require plugin_dir_path( __FILE__ ) . 'includes/class-featureflags.php';
require plugin_dir_path( __FILE__ ) . 'includes/admin/settings-page.php';
require plugin_dir_path( __FILE__ ) . 'includes/api/api.general.php';

/**
 * AJAX Action toggling features from the WP admin area.
 */
add_action( 'wp_ajax_featureFlag_enable', 'feature_flag_enable' );

/**
 * Enable a feature Flag
 */
function feature_flag_enable() {

	// input var okay;
	if ( isset( $_POST['featureKey'] ) && check_ajax_referer( 'featureFlagNonce', 'security' ) ) { // input var okay;

		$response = [];

		$feature_key = sanitize_text_field( wp_unslash( $_POST['featureKey'] ) ); // input var okay;

		if ( ! empty( $feature_key ) ) {

			$response['response'] = $feature_key;

			FeatureFlags::init()->toggle_feature( $feature_key );

		} else {

			header( 'HTTP/1.1 500 Internal Server Error' );
			$response['response'] = 'no feature key';

		}

		header( 'Content-Type: application/json' );
		echo wp_json_encode( $response );

	}

	exit();

}

/**
 * Redirect the user to the login form if they attempt to use a
 * flag query string while logged out.
 */
function redirect_with_key() {

	if ( isset( $_SERVER['REQUEST_URI'] ) && ! empty( find_query_string() && ! is_user_logged_in() ) ) {

		$destination = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // input var okay;

		if ( filter_var( $destination, FILTER_VALIDATE_URL ) ) {
			wp_safe_redirect( wp_login_url( $destination ) );
			exit();
		}
	}
}

add_action( 'template_redirect', 'redirect_with_key' );

/**
 * Check if there is a flag query string.
 *
 * @return bool|string False if there isn't one, the flag string if found.
 */
function find_query_string() {

	/* TODO: Make this a configurable key */
	$query_string_key = 'flag';

	// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, Wordpress.VIP.SuperGlobalInputUsage.AccessDetected
	if ( isset( $_GET[ $query_string_key ] ) && '' !== $_GET[ $query_string_key ] ) {  // input var okay;

		// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, Wordpress.VIP.SuperGlobalInputUsage.AccessDetected
		return sanitize_title( wp_unslash( $_GET[ $query_string_key ] ) );  // input var okay;

	} else {

		return false;
	}
}
