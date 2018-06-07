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
	include FF_PLUGIN_PATH.'incs/class.php';
	include FF_PLUGIN_PATH.'incs/admin/settings-page.php';

	/**
	 * Register a feature flag with the plugin.
	 *
	 * @param [Array] $args
	 * @return void
	 */
	function register_featureFlag($args){

		$defaults = array(

			'enforced' => false,
			'description' => '',

		);

		$args = wp_parse_args($args, $defaults);

		if( isset($args['title']) && isset($args['key']) ){

			featureFlags::init()->add_flag($args);

		} else {

			add_action( 'admin_notices', function($args){

				$class = 'notice notice-error';
				$message = 'Malformed featureFlag - Need to supply a key and a title.';

				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 

			});

		}		
		
	}

	function debug(){

		return featureFlags::init()->getUserSettings(); 

	}

	function hasUserEnabled($featureKey = ''){

		return featureFlags::init()->hasUserEnabled($featureKey);

	}

	function isEnabled($featureKey = ''){

		return featureFlags::init()->isEnabled($featureKey);

	}

	/**
	 * AJAX Action
	 */
	add_action('wp_ajax_featureFlag_enable', 'featureFlagEnable');

	function featureFlagEnable(){

			$reponse = array();

			$featureKey = $_POST['featureKey'];

			if(!empty($featureKey)){
				
				// Do fun plugin stuff
				$response['response'] = $featureKey;

				featureFlags::init()->toggleFeature($featureKey);
			
			} else {
				
				header('HTTP/1.1 500 Internal Server Error');
				$response['response'] = "no feature key";
			
			}
			
			header( "Content-Type: application/json" );
			echo json_encode($response);

			//Don't forget to always exit in the ajax function.
			exit();

	}