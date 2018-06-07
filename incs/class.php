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

					return hasUserEnabled($flagKey);

				}

			} else {

				return false;

			}

		}

		function getUserSettings(){

			$user_id = get_current_user_id();

			if($user_id){

				return get_user_meta( $user_id, 'enabledFlags', true);

			} else {

				return false;

			}

		}

		function hasUserEnabled($featureKey){

			$user_id = get_current_user_id();
			$response = false;

			if($user_id){

				// We have a user
				$user_settings = get_user_meta( $user_id, 'enabledFlags', true);

				// Other

				$response = (isset($user_settings[$featureKey]) ? $user_settings[$featureKey] : false);

			}

			return $response;

		}

		/**
		 * Undocumented function
		 *
		 * @return void
		 */
		function toggleFeature($featureKey){

			$user_id = get_current_user_id();

			if($user_id){

				$user_settings = get_user_meta( $user_id, 'enabledFlags', true);

				$enabled = ( $user_settings ?: [] );

				if($enabled[$featureKey]){

					$enabled[$featureKey] = !$enabled[$featureKey];

				} else {

					$enabled[$featureKey] = true;

				}

				update_user_meta( $user_id, 'enabledFlags', $enabled);

			}

		}

	}







