<?php
/**
 *
 * @package   Feature Flags
 * @author    James Williams <james@jamesrwilliams.co.uk>
 * @link      https://jamesrwilliams.co.uk/
 * @copyright 2018 James Williams
 *
 * @wordpress-plugin
 * Plugin Name:       Feature Flags
 * Description:       Easily register and work with feature flags in your theme.
 * Version:           1.0.0
 * Author:            James Williams
 * Author URI:        https://jamesrwilliams.co.uk/
 *
 */

/* If this file is called directly abort.
-------------------------------------------------------- */
if (!defined('WPINC')) wp_die();

/* Define plugin paths and url for global usage
-------------------------------------------------------- */
define('FF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FM_PLUGIN_URL', plugin_dir_url(__FILE__));

/* On plugin activation
-------------------------------------------------------- */
register_activation_hook(__FILE__, function() {});

/* Includes
-------------------------------------------------------- */
include FF_PLUGIN_PATH.'includes/class.feature_flags.php';
include FF_PLUGIN_PATH.'includes/admin/settings_page.php';
include FF_PLUGIN_PATH.'includes/api/api.general.php';

/**
 * AJAX Action toggling features from the WP admin area.
 */
add_action('wp_ajax_featureFlag_enable', 'featureFlagEnable');

function featureFlagEnable(){

	$response = array();

	$featureKey = $_POST['featureKey'];

	if(!empty($featureKey)){
		
		// Do fun plugin stuff
		$response['response'] = $featureKey;

		featureFlags::init()->toggle_feature($featureKey);
	
	} else {
		
		header('HTTP/1.1 500 Internal Server Error');
		$response['response'] = "no feature key";
	
	}
	
	header( "Content-Type: application/json" );
	echo json_encode($response);

	//Don't forget to always exit in the ajax function.
	exit();

}