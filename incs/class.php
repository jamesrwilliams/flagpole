<?php

	class featureFlags {

		protected $flags;

		/* Constructor
		-------------------------------------------------------- */
		function __construct() {
			
			// Construct

		}

		public function register($args){

			$defaults = array(

				'title' => '',
				'key' => '',
				'stable' => false

			);

			$args = wp_parse_args( $args, $defaults );

		}

	}








