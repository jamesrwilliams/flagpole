<?php
/**
 * FeatureFlag Class
 *
 * Used for creating feature flags.
 *
 * @package   feature-flags
 * @author    James Williams <james@jamesrwilliams.co.uk>
 * @link      https://github.com/jamesrwilliams/feature-flags
 * @copyright 2018 James Williams
 */

namespace FeatureFlags;

use FeatureFlag\Flag;

require_once 'class-flag.php';

/**
 * Class featureFlags
 *
 * @package FeatureFlags
 */
class FeatureFlags {

	/**
	 * The class instance. Only need one of these.
	 *
	 * @var object Class Instance
	 */
	private static $instance;

	/**
	 * The user meta key used for db access.
	 *
	 * @var string $user_meta_key
	 */
	private static $user_meta_key = 'enabledFlags';

	/**
	 * Current Feature Flags
	 *
	 * @var array $flags
	 */
	public $flags = [];

	/**
	 * Static function to create an instance if none exists
	 */
	public static function init() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Add a new flag to the plugin register.
	 *
	 * @param array $flag TODO Add a flag to the system.
	 *
	 * @return void
	 */
	public function add_flag( $flag ) {

		$this->flags[] = new Flag( $flag['key'], $flag['title'], $flag['enforced'], $flag['description'] );

	}

	/**
	 * Retrieve the flag object of a specified key.
	 *
	 * @param string $key The flag key we're looking for.
	 *
	 * @return \FeatureFlag\Flag.
	 */
	public function find_flag( $key ) {

		$flag  = false;
		$flags = $this->flags;

		foreach ( $flags as $struct ) {
			if ( $key === $struct->key ) {
				$flag = $struct;
				break;
			}
		}

		return $flag;

	}

	/**
	 * Undocumented function
	 *
	 * @param boolean $enforced TODO Is the are we getting all enforce flags also.
	 *
	 * @return array All available flags if $enforced is false, else only returns 'enforced' features.
	 */
	public function get_flags( $enforced = false ) {

		$flags = $this->flags;

		if ( $enforced ) {

			$filtered_flags = array_filter( $flags, function ( $value ) {

				return $value->get_enforced();

			} );

		} else {

			$filtered_flags = array_filter( $flags, function ( $value ) {

				return ! $value->get_enforced();

			} );

		}

		return $filtered_flags;

	}

	/**
	 * Check if the provided key is currently enabled.
	 *
	 * @param string $flag_key The key of the flag we're looking for.
	 *
	 * @return boolean Is the flag enabled or not.
	 */
	public function is_enabled( $flag_key ) {

		$export = $this->find_flag( $flag_key );

		if ( $export ) {

			$enforced = $export->get_enforced();

			if ( $enforced ) {

				return true;

			} else {

				return has_user_enabled( $flag_key );
			}
		} else {

			return false;

		}

	}

	/**
	 * Get the current users' settings.
	 *
	 * @return bool|array The current user's settings array.
	 */
	public function get_user_settings() {

		$user_id = get_current_user_id();

		if ( ! empty( $user_id ) ) {

			return get_user_meta( $user_id, 'enabledFlags', true );

		} else {

			return false;

		}

	}

	/**
	 * Check if the current WordPress user has enabled the provided feature.
	 *
	 * @param string $feature_key The feature key we're checking.
	 *
	 * @return bool
	 */
	public function has_user_enabled( $feature_key ) {

		$user_id  = get_current_user_id();
		$response = false;

		if ( $user_id ) {

			// We have a user.
			$user_settings = get_user_meta( $user_id, 'enabledFlags', true );

			// Other.
			$response = ( isset( $user_settings[ $feature_key ] ) ? $user_settings[ $feature_key ] : false );

		}

		return $response;

	}

	/**
	 * Toggle the feature for the current user.
	 *
	 * @param string $feature_key The feature key we're checking.
	 *
	 * @return void
	 */
	public function toggle_feature( $feature_key ) {

		$user_id = get_current_user_id();

		if ( $user_id ) {

			$user_settings = get_user_meta( $user_id, 'enabledFlags', true );

			$enabled = ( $user_settings ?: [] );

			if ( $enabled[ $feature_key ] ) {

				$enabled[ $feature_key ] = ! $enabled[ $feature_key ];

			} else {

				$enabled[ $feature_key ] = true;

			}

			update_user_meta( $user_id, 'enabledFlags', $enabled );

		}

	}

}







