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