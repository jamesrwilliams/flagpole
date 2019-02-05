<?php
/**
 * Feature Flags for WordPress.
 * Include things in your WordPress theme using Feature Flags.
 *
 * @package   wp-feature-flags
 * @author    James Williams <james@jamesrwilliams.ca>
 * @link      https://jamesrwilliams.ca/
 * @copyright 2019 James Williams
 *
 * @wordpress-plugin
 * Plugin Name:       Feature Flags
 * Description:       Easily register and work with feature flags in your theme.
 * Version:           1.0.0
 * Author:            James Williams
 * Text Domain:       featureFlags
 * Author URI:        https://jamesrwilliams.ca/
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
	register_setting(
		'ff-settings-page',
		'ff_client_secret',
		function ( $posted_data ) {
			if ( ! $posted_data ) {
				add_settings_error( 'ff_client_secret', 'ff_updated', 'Error Message', 'error' );
				return false;
			}
			return $posted_data;
		}
	);
});

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
require plugin_dir_path( __FILE__ ) . 'includes/api/api.shortcode.php';

/**
 * AJAX Action toggling features from the WP admin area.
 */
add_action( 'wp_ajax_toggleFeatureFlag', 'feature_flag_enable' );

/**
 * Enable a feature Flag
 */
function feature_flag_enable() {

	if ( isset( $_POST['featureKey'] ) && check_ajax_referer( 'featureFlagNonce', 'security' ) ) { // input var okay;

		$response = [];

		$feature_key = sanitize_text_field( wp_unslash( $_POST['featureKey'] ) ); // input var okay;

		if ( ! empty( $feature_key ) ) {

			$response['response'] = $feature_key;

			FeatureFlags::init()->toggle_feature_preview( $feature_key );

		} else {

			header( 'HTTP/1.1 500 Internal Server Error' );
			$response['response'] = 'no feature key';

		}
	}

	exit();

}

/**
 * AJAX Action toggling features from the WP admin area.
 */
add_action( 'wp_ajax_togglePublishedFeature', 'feature_flag_publish' );

/**
 * Publish a feature flag to be publicly visible.
 */
function feature_flag_publish() {

	// input var okay;
	if ( isset( $_POST['featureKey'] ) && check_ajax_referer( 'featureFlagNonce', 'security' ) ) { // input var okay;

		$response = [];

		$feature_key = sanitize_text_field( wp_unslash( $_POST['featureKey'] ) ); // input var okay;

		if ( ! empty( $feature_key ) ) {

			$response['response'] = FeatureFlags::init()->toggle_feature_publication( $feature_key );

		} else {

			header( 'HTTP/1.1 500 Internal Server Error' );
			$response['response'] = 'no feature key';

		}

		header( 'Content-Type: application/json' );
		echo wp_json_encode( $response );
	}

	exit();
}

// Groups - Register
add_action( 'admin_post_ff_register_group', 'feature_flag_create_group' );
add_action( 'admin_post_nopriv_ff_register_group', 'feature_flag_create_group' );

/**
 * Create a new flag group
 */
function feature_flag_create_group() {

	$validation = [];
	$response   = [];

	$validation['group-nonce'] = check_admin_referer( 'register-group' );
	$validation['group-key']    = ( ! empty( $_GET['group-key'] ) ? sanitize_text_field( wp_unslash( $_GET['group-key'] ) ) : false ); // input var okay;
	$validation['group-name']  = ( ! empty( $_GET['group-name'] ) ? sanitize_text_field( wp_unslash( $_GET['group-name'] ) ) : false ); // input var okay;
	$validation['group-desc']  = ( ! empty( $_GET['group-description'] ) ? sanitize_textarea_field( wp_unslash( $_GET['group-description'] ) ) : false ); // input var okay;

	$validation = array_filter( $validation );

	if ( $validation ) {

		$response['response'] = FeatureFlags::init()->create_group( $validation['group-key'], $validation['group-name'], $validation['group-desc'] );

		if ( wp_get_referer() ) {
			$dest = wp_get_referer();
		} else {
			$dest = get_home_url();
		}

		$dest = add_query_arg(
			[ 'error' => 'g1' ],
			$dest
		);

		wp_safe_redirect( $dest );

	}

	exit();

}

// Groups - Add too
add_action( 'admin_post_ff_add_to_group', 'feature_flag_add_to_group' );
add_action( 'admin_post_nopriv_ff_add_to_group', 'feature_flag_add_to_group' );

/**
 * TODO - Adding a flag to a group.
 */
function feature_flag_add_to_group() {
	// TODO Adding flags to groups.
}

add_action( 'template_redirect', 'feature_flag_redirect_with_key' );

/**
 * Redirect the user to the login form if they attempt to use a
 * flag query string while logged out.
 */
function feature_flag_redirect_with_key() {

	$query = find_query_string();

	if ( isset( $_SERVER['REQUEST_URI'] ) && isset( $_SERVER['HTTP_HOST'] ) && ! empty( $query ) && ! is_user_logged_in() ) { // input var okay;

		if ( FeatureFlags::init()->is_private( $query ) ) {
			$destination = esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ); // input var okay;

			if ( filter_var( $destination, FILTER_VALIDATE_URL ) ) {
				wp_safe_redirect( wp_login_url( $destination ) );
				exit();
			}
		}
	}
}

/**
 * Check if there is a flag query string.
 *
 * @return bool|string False if there isn't one, the flag string if found.
 */
function find_query_string() {

	/* TODO: #12 - Make this a configurable key */
	$query_string_key = 'flag';

	// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, Wordpress.VIP.SuperGlobalInputUsage.AccessDetected
	if ( isset( $_GET[ $query_string_key ] ) && '' !== $_GET[ $query_string_key ] ) {  // input var okay;

		// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, Wordpress.VIP.SuperGlobalInputUsage.AccessDetected
		return sanitize_title( wp_unslash( $_GET[ $query_string_key ] ) );  // input var okay;

	} else {

		return false;
	}
}

add_shortcode( 'debugFeatureFlags', 'shortcode_debug' );
