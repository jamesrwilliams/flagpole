<?php
	
	class featureFlags {
		
		private static $instance;

		public $example;
		public $flags;

		/**
		 * Static function to create an instance if none exists
		 */
		public static function init() {
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
		
			$this->flags = [];

		}

		function exampleCall(){

			return $this->example;

		}

		function get_flags(){

			return $this->flags;

		}

		function add_flag($flag){

			$this->flags[] = $flag;

		}

	}

	function featureFlags_register($args){

		$defaults = array(

			'title' => '',
			'key' => '',
			'status' => 0

		);

		$args = wp_parse_args($args, $defaults);

		featureFlags::init()->add_flag($args);
		
	}

	function run_example() {

		return featureFlags::init()->exampleCall();
	
	}








