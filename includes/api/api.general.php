<?php

  /**
	 * Register a feature flag with the plugin.
	 *
	 * @param [Array] $args
	 * @return void
	 */
	function register_feature_flag($args){

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

	function has_user_enabled($featureKey = ''){

		return featureFlags::init()->has_user_enabled($featureKey);

	}

	function is_enabled($featureKey = ''){

		return featureFlags::init()->is_enabled($featureKey);

	}

?>