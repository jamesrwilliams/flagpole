<?php
	
	include_once 'class.flag.php';

	class featureFlags {
		
		private static $instance;

		public $example;
		public $flags = [];

		public $statusMap = [

			0 => 'Alpha - Available locally only - cannot be enabled via the admin area',
			1 => 'Beta - Available to users - disabled by default',
			2 => 'Production - Enabled for all site users.',

		];

		/**
		 * Static function to create an instance if none exists
		 */
		public static function init() {
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		function __construct() {}

		function get_flags(){

			return $this->flags;

		}

		function add_flag($flag){

			$this->flags[] = new Flag($flag['key'], $flag['title'], $flag['status'], $flag['description']);

		}

		function functionIsEnabled(){

			// Return bool feature status;

		}

		function getStatus($flagKey){

			$flag = getFlag($flagKey);

		}

	}







