<?php
	
	include_once 'class.flag.php';

	class featureFlags {
		
		private static $instance;
		private static $USER_META_KEY = 'enabledFlags';

		public $example;
		public $flags = [];

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

			$this->flags[] = new Flag($flag['key'], $flag['title'], $flag['enforced'], $flag['description']);

		}

		/**
		 * Retrieve the flag object of a specified key.
		 *
		 * @param string $key
		 * @return void
		 */
		function findFlag($key){

			$flag = false;
			$flags = $this->flags;

			foreach($flags as $struct) {
					if ($key == $struct->key) {
							$flag = $struct;
							break;
					}
			}

			return $flag;

		}

		/**
		 * Undocumented function
		 *
		 * @param [type] $flagKey
		 * @return boolean
		 */
		function isEnabled($flagKey){

			$export = $this->findFlag($flagKey);

			if($export){

				$enforced = $export->get_enforced();

				if($enforced){

					return true;

				} else {

					// 2. hasUserEnabled($featureKey);
					return false;

				}

			} else {

				return false;

			}

		}

		function hasUserEnabled($featureKey){

			$user_id = get_current_user_id();
			$response = false;

			if($user_id){

				// We have a user
				// $user_settings = get_user_meta( $user_id, $this->USER_META_KEY, );

			}

			return $response;

		}

		/**
		 * Undocumented function
		 *
		 * @return void
		 */
		function enableFeature($featureKey){

			$user_id = get_current_user_id();

			if($user_id){

				$user_settings = get_user_meta( $user_id, $this->USER_META_KEY);

				$append = [];

				$append[$featureKey] = true;

				$user_settings[] = $append[$featureKey];

				update_user_meta( $user_id, $this->USER_META_KEY, $user_settings);

			}

		}

		function disableFeature($featureKey){

			

		}

	}







