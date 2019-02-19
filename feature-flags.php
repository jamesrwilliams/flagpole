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
add_action(
	'admin_init',
	function () {
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
	}
);

/**
 * Plugin styles and scripts.
 *
 * @param string $hook The Hook string.
 */
function feature_flags_admin_imports( $hook ) {

	if ( 'tools_page_feature-flags' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'feature-flags-styles',
		plugins_url(
			'/assets/css/feature-flags.css',
			__FILE__
		),
		[],
		FF_VERSION
	);

	wp_register_script(
		'feature-flags-script',
		plugins_url(
			'/assets/js/feature-flags.js',
			__FILE__
		),
		[],
		FF_VERSION,
		false
	);

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

	if (
		isset( $_POST['featureKey'] ) &&
		check_ajax_referer( 'featureFlagNonce', 'security' )
	) {

		$response = [];

		$feature_key = sanitize_text_field( wp_unslash( $_POST['featureKey'] ) );

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

	if ( isset( $_POST['featureKey'] ) && check_ajax_referer( 'featureFlagNonce', 'security' ) ) {

		$response = [];

		$feature_key = sanitize_text_field( wp_unslash( $_POST['featureKey'] ) );

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

// Groups - Create.
add_action( 'admin_post_ff_register_group', 'feature_flag_create_group' );
add_action( 'admin_post_nopriv_ff_register_group', 'feature_flag_create_group' );

/**
 * Create group admin hook handler.
 */
function feature_flag_create_group() {

	$validation                  = [];
	$validation['group-nonce']   = check_admin_referer( 'register-group' );
	$validation['group-key']     = ( ! empty( $_GET['group-key'] ) ? sanitize_text_field( wp_unslash( $_GET['group-key'] ) ) : false );
	$validation['group-name']    = ( ! empty( $_GET['group-name'] ) ? sanitize_text_field( wp_unslash( $_GET['group-name'] ) ) : false );
	$validation['group-desc']    = ( ! empty( $_GET['group-description'] ) ? sanitize_textarea_field( wp_unslash( $_GET['group-description'] ) ) : '' );
	$validation['group-private'] = ( ! empty( $_GET['group-private'] ) ? sanitize_textarea_field( wp_unslash( $_GET['group-private'] ) ) : false );

	$validation = array_filter( $validation );

	if ( $validation ) {

		$result = FeatureFlags::init()->create_group( $validation['group-key'], $validation['group-name'], $validation['group-desc'], $validation['group-private'] );

		feature_flag_operation_redirect( $result );

	}
}

// Groups - Delete.
add_action( 'admin_post_ff_delete_group', 'feature_flag_delete_group' );
add_action( 'admin_post_nopriv_ff_delete_group', 'feature_flag_delete_group' );

/**
 * Delete group admin hook handler.
 */
function feature_flag_delete_group() {

	if ( ! empty( $_GET['key'] ) && check_admin_referer( 'ff_delete_group' ) ) {
		$key = sanitize_text_field( wp_unslash( $_GET['key'] ) );
	} else {
		$key = false;
	}

	$result = FeatureFlags::init()->delete_group( $key );

	feature_flag_operation_redirect( $result );

}

add_action( 'admin_post_ff_preview_group', 'feature_flag_toggle_group_preview' );
add_action( 'admin_post_nopriv_ff_preview_group', 'feature_flag_toggle_group_preview' );

/**
 * Toggle group preview handler.
 */
function feature_flag_toggle_group_preview() {

	if ( ! empty( $_GET['group_key'] ) && check_admin_referer( 'ff_preview_group' ) ) {
		$group_key = sanitize_text_field( wp_unslash( $_GET['group_key'] ) );
	} else {
		$group_key = false;
	}

	$result = FeatureFlags::init()->toggle_group_preview( $group_key );

	feature_flag_operation_redirect( $result );
}

// Groups - Add too group.
add_action( 'admin_post_ff_add_to_group', 'feature_flag_add_to_group' );
add_action( 'admin_post_nopriv_ff_add_to_group', 'feature_flag_add_to_group' );

/**
 * Add to group admin hook handler.
 */
function feature_flag_add_to_group() {

	if (
		! empty( $_GET['selected-flag'] ) &&
		! empty( $_GET['selected-group'] ) &&
		check_admin_referer( 'ff-add-to-group' )
	) {
		$flag  = sanitize_text_field( wp_unslash( $_GET['selected-flag'] ) );
		$group = sanitize_text_field( wp_unslash( $_GET['selected-group'] ) );

		$response = FeatureFlags::init()->add_flag_to_group( $flag, $group );

		feature_flag_operation_redirect( $response );
	} else {
		feature_flag_operation_redirect( 'fgae' );
	}
}

add_action( 'admin_post_ff_remove_flag_from_group', 'feature_flag_remove_from_group' );
add_action( 'admin_post_nopriv_remove_flag_from_group', 'feature_flag_remove_from_group' );

/**
 * Admin Post handler for removing a flag from a group.
 */
function feature_flag_remove_from_group() {
	if (
		! empty( $_GET['flag'] ) &&
		! empty( $_GET['group'] ) &&
		check_admin_referer( 'ff_remove_flag_from_group' )
	) {
		$flag  = sanitize_text_field( wp_unslash( $_GET['flag'] ) );
		$group = sanitize_text_field( wp_unslash( $_GET['group'] ) );

		$response = FeatureFlags::init()->remove_flag_from_group( $flag, $group );

		feature_flag_operation_redirect( $response );
	} else {
		feature_flag_operation_redirect( 'fgae' );
	}
}

add_action( 'template_redirect', 'feature_flag_redirect_with_key' );

/**
 * Redirect the user to the login form if they attempt to use a
 * flag query string while logged out.
 */
function feature_flag_redirect_with_key() {

	$query = find_query_string();

	if (
		isset( $_SERVER['REQUEST_URI'] ) &&
		isset( $_SERVER['HTTP_HOST'] ) &&
		! empty( $query ) && ! is_user_logged_in()
	) {

		if ( FeatureFlags::init()->is_private( $query ) ) {
			$destination = esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) );

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

	/* TODO: #21 - Make this a configurable key */
	$query_string_key = 'flag';

	// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, Wordpress.VIP.SuperGlobalInputUsage.AccessDetected
	if ( isset( $_GET[ $query_string_key ] ) && '' !== $_GET[ $query_string_key ] ) {

		// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, Wordpress.VIP.SuperGlobalInputUsage.AccessDetected
		return sanitize_title( wp_unslash( $_GET[ $query_string_key ] ) );

	} else {

		return false;
	}
}

/**
 * Quickly generate return URLs with appropriate Error code messages.
 *
 * @param bool $error_code The error code string.
 * @param bool $redirect Either safe_redirect or return the URL.
 *
 * @see FeatureFlags::get_admin_error_message() Method for generating error messages.
 *
 * @return null|string Depending on $redirect.
 */
function feature_flag_operation_redirect( $error_code = false, $redirect = true ) {

	$redirect_url = ( wp_get_referer() ?: get_home_url() );

	if ( false !== $error_code && ! empty( $error_code ) ) {
		$redirect_url = add_query_arg(
			[
				'error' => $error_code,
			],
			$redirect_url
		);
	}

	if ( $redirect ) {
		wp_safe_redirect( $redirect_url );
	} else {
		return $redirect_url;
	}
}

add_shortcode( 'debugFeatureFlags', 'shortcode_debug' );
