<?php
/**
 * FeatureFlag Class
 *
 * Used for creating feature flags.
 *
 * @package   wp-feature-flags
 * @author    James Williams <james@jamesrwilliams.ca>
 * @link      https://github.com/jamesrwilliams/wp-feature-flags
 * @copyright 2019 James Williams
 */

namespace FeatureFlags;

require_once 'class-flag.php';
require_once 'class-group.php';

use FeatureFlag\Flag;
use FeatureFlag\Group;

/**
 * Class FeatureFlags
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
	 * The user meta key used for wp_options access.
	 *
	 * @var string $user_meta_key
	 */
	private static $meta_prefix = 'feature_flags_';

	/**
	 * Current Feature Flags
	 *
	 * @var array $flags
	 */
	public $flags = [];

	/**
	 * Current feature groups.
	 *
	 * @var array $groups
	 */
	public $groups = [];

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
	 * FeatureFlags constructor.
	 */
	public function __construct() {

		// Initialise the groups wp_options row for future use.
		$key         = self::get_options_key() . 'groups';
		$flag_groups = maybe_unserialize( get_option( $key ) );
		$type        = gettype( $flag_groups );

		if ( 'array' !== $type ) {
			$flag_groups = [];
			add_option( $key, maybe_serialize( $flag_groups ) );
		}

		$this->groups = $flag_groups;

	}

	/**
	 * Return the meta key for WP_options storage.
	 *
	 * @return string
	 */
	public function get_options_key() {
		return self::$meta_prefix;
	}

	/**
	 * Return the appropriate error message for the error code provided.
	 *
	 * @param int $index Error code in the array.
	 *
	 * @return string The error message associated with the $index.
	 */
	public function get_admin_error_message( $index = 0 ) {

		$messages = [
			0                       => 'An unknown error occurred. Please check the error logs for more information.',
			'group-created'         => 'Flag group created successfully.',
			'gr'                    => false,
			'gu'                    => 'Flag group updated successfully.',
			'gd'                    => 'Flag group successfully deleted.',
			'fpc'                   => 'Flag enabled.',
			'fd'                    => 'Flag disabled.',
			'fga'                   => 'Flag added to group successfully.',
			'fgae'                  => 'A problem occurred adding the flag to the group.',
			'fgd'                   => 'Flag removed from group successfully.',
			'fgde'                  => 'A problem occurred removing the flag from the group.',
			'flag-already-in-group' => 'This flag is already in this group.',
			'group-with-key-exists' => 'A group with the key already exists',

		];

		return ( ! empty( $messages[ $index ] ) ? $messages[ $index ] : $messages[0] );
	}

	/**
	 * Return the appropriate status class for the error code provided.
	 *
	 * @param int $index Error code for the array.
	 *
	 * @return string The associated class for the WordPress admin notice.
	 */
	public function get_admin_message_class( $index = 0 ) {
		$statuses = [
			0                       => 'error',
			'gc'                    => 'success',
			'gu'                    => 'success',
			'gd'                    => 'success',
			'fga'                   => 'success',
			'fgd'                   => 'success',
			'flag-already-in-group' => 'warning',
		];

		return ( ! empty( $statuses[ $index ] ) ? $statuses[ $index ] : $statuses[0] );
	}

	/**
	 * Add a new flag to the plugin register.
	 *
	 * @param array $flag The flag object to add.
	 *
	 * @return void
	 */
	public function add_flag( $flag ) {

		$this->flags[] = new Flag( $flag['key'], $flag['title'], $flag['enforced'], $flag['description'], $flag['stable'] );

	}

	/**
	 * Retrieve the flag object of a specified key.
	 *
	 * @param string $key The flag key we're looking for.
	 * @param bool   $check Return either if it's a valid flag or the flag itself.
	 *
	 * @return \FeatureFlag\Flag|bool.
	 */
	public function find_flag( $key, $check = false ) {

		$flag  = false;
		$flags = $this->flags;

		foreach ( $flags as $struct ) {
			if ( $key === $struct->key ) {
				$flag = $struct;
				break;
			}
		}

		return ( $check ? true : $flag );

	}

	/**
	 * Get all the current flags.
	 *
	 * @param boolean $enforced Fetch enforced flags or just regular ones.
	 *
	 * @return array All available flags if $enforced is false, else only returns 'enforced' features.
	 */
	public function get_flags( $enforced = false ) {

		$flags = $this->flags;

		if ( $enforced ) {

			$filtered_flags = array_filter(
				$flags,
				function ( $value ) {
					return $value->get_enforced();
				}
			);

		} else {

			$filtered_flags = array_filter(
				$flags,
				function( $value ) {
					return ! $value->get_enforced();
				}
			);

		}

		return $filtered_flags;

	}

	/**
	 * Check if the provided key is currently enabled.
	 *
	 * @param string  $feature_key The key of the flag we're looking for.
	 * @param boolean $reason Option to return reason why a flag is enabled.
	 *
	 * @return boolean Is the flag enabled or not.
	 */
	public function is_enabled( $feature_key, $reason = false ) {

		$export = $this->find_flag( $feature_key );

		if ( $export ) {

			$published = $export->is_published();

			// Commenting this out as I've ripped up the flag query string system.
			// $query  = $this->check_query_string( $feature_key ); .
			$query    = false;
			$enforced = $export->get_enforced();

			if ( $published ) {
				return ( $reason ? 'Published' : true );
			} elseif ( $enforced ) {
				return ( $reason ? 'Enforced' : true );
			} else {

				if ( $query ) {
					return ( $reason ? 'Using query string' : true );
				} elseif ( has_user_enabled( $feature_key ) ) {
					return ( $reason ? 'User preview' : true );
				} else {
					return ( $reason ? '' : null );
				}
			}
		} else {

			/**
			 * We want to display an error if WP_DEBUG is enabled to highlight an
			 * unregistered flag key is being checked.
			 */

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( 'Feature not registered.', E_USER_WARNING );

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

			return self::get_user( $user_id, self::$meta_prefix, true );

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
			$user_settings = self::get_user( $user_id, self::$meta_prefix, true );

			// Other.
			$response = ( isset( $user_settings[ $feature_key ] ) ? $user_settings[ $feature_key ] : false );

		}

		return $response;

	}

	/**
	 * Check if a provided group key requires logging in.
	 *
	 * @param string $group_key The feature we're checking.
	 * @return bool Is the feature private or not.
	 */
	public function is_private( $group_key ) {
		return self::get_group( $group_key )->private;
	}

	/**
	 * Toggle the feature for the current user.
	 *
	 * @param string $feature_key The feature key we're checking.
	 * @return void
	 */
	public function toggle_feature_preview( $feature_key ) {

		$user_id = get_current_user_id();

		if ( $user_id ) {

			$user_settings = self::get_user( $user_id, self::$meta_prefix, true );

			$enabled = ( $user_settings ?: [] );

			if ( $enabled[ $feature_key ] ) {

				$enabled[ $feature_key ] = ! $enabled[ $feature_key ];

			} else {

				$enabled[ $feature_key ] = true;

			}

			self::update_user( $user_id, self::$meta_prefix, $enabled );

		}

	}

	/**
	 * Toggles a feature for publication.
	 *
	 * @param Flag $feature_key The key of the feature to toggle publication status.
	 *
	 * @return void|string JSON response if error.
	 */
	public function toggle_feature_publication( $feature_key ) {

		$key             = self::$meta_prefix . 'flags';
		$published_flags = maybe_unserialize( get_option( $key ) );
		$options_type    = gettype( $published_flags );

		if ( 'array' !== $options_type ) {
			$published_flags = [];
			add_option( $key, maybe_serialize( $published_flags ) );

		}

		$found_in_options = array_search( $feature_key, $published_flags, true );

		if ( false === $found_in_options || - 1 === $found_in_options ) {

			if ( self::find_flag( $feature_key )->stable !== true ) {
				return wp_json_encode( 'This feature is unstable.' );
			} else {
				$published_flags[] = $feature_key;
			}
		} else {
			unset( $published_flags[ $found_in_options ] );
		}

		update_option( $key, $published_flags, true );

	}

	/**
	 * Conditional wrapper for get_user_meta based on WordPress VIP or regular.
	 *
	 * @param integer $user_id The ID of the user whose data should be retrieved.
	 * @param string  $key The key for the meta_value to be returned.
	 * @param bool    $single If true return value of meta data field, if false return an array.
	 *
	 * @return mixed
	 */
	private function get_user( $user_id, $key, $single = true ) {

		if ( function_exists( 'get_user_attribute' ) ) {
			return get_user_attribute( $user_id, $key );
		} else {
			// phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_get_user_meta
			return get_user_meta( $user_id, $key, $single );
		}

	}

	/**
	 * Conditional wrapper for update_user_meta based on WordPress VIP or regular.
	 *
	 * @param integer $user_id User ID.
	 * @param string  $meta_key The key for the meta_value to be updated.
	 * @param mixed   $meta_value The new desired value of the meta_key, which must be different from the existing value.
	 * @param string  $prev_value Previous value to check before removing.
	 *
	 * @return bool|int
	 */
	private function update_user( $user_id, $meta_key, $meta_value, $prev_value = '' ) {

		if ( function_exists( 'update_user_attribute' ) ) {
			return update_user_attribute( $user_id, $meta_key, $meta_value );
		} else {
			// phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_update_user_meta
			return update_user_meta( $user_id, $meta_key, $meta_value, $prev_value );
		}

	}

	/**
	 * Check if a query argument has been passed to enable a flag manually.
	 * Also validates it's publicly queryable.
	 *
	 * @param string $feature_key The key of the flag we're aiming to match.
	 * @return bool Is there a query string for this flag currently?
	 */
	public function check_query_string( $feature_key ) {

		$query = find_query_string();

		if ( ! empty( $query ) && $query ) {

			if ( self::is_querable( $query ) ) {
				return $query === $feature_key;
			} else {
				/**
				 * We want to display an error if WP_DEBUG is enabled to highlight a
				 * flag being queried that has not been enabled.
				 */

				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
				trigger_error( 'Trying to query flag that is not queryable.', E_USER_WARNING );

				return false;

			}
		} else {
			return false;
		}
	}

	/**
	 * Register a new Flag group.
	 *
	 * @param string $key string The Group key.
	 * @param string $name string The Group name to create.
	 * @param string $description string Optional description for the group.
	 * @param bool   $private Is this group publicly accessible.
	 *
	 * @return string Error response message key.
	 */
	public function create_group( $key, $name, $description = '', $private = false ) {

		$sanitised_key = sanitize_title( $key );

		$group_exists = self::get_group( $sanitised_key, true );

		if ( true === $group_exists ) {
			return 'group-with-key-exists';
		} else {

			$status = ( 'true' === $private ? true : false );

			$new_group = new Group( $sanitised_key, $name, $description, $status );

			$groups = self::get_groups();

			$groups[] = $new_group;

			self::save_groups( $groups );

			return 'group-created';
		}

	}

	/**
	 * Update meta information about a flag group.
	 *
	 * @param string $key The feature group's key.
	 * @param array  $args The parameters to update for $key.
	 */
	public function update_group( $key, $args = [] ) {
		// TODO Implement "Update" using variable args array.
	}

	/**
	 * Add a flag to a group.
	 *
	 * @param string $flag_key The key we want to add to a group.
	 * @param string $group_key The group we want to add a key to.
	 *
	 * @return int|string
	 */
	public function add_flag_to_group( $flag_key, $group_key ) {

		$flag   = self::find_flag( $flag_key );
		$group  = self::get_group( $group_key, false, true );
		$groups = self::get_groups();

		if ( false !== $flag && false !== $group ) {
			$result = $groups[ $group ]->add_flag( $flag_key );

			if ( $result ) {
				self::save_groups( $groups );
				return 'fga';
			} else {
				return 'flag-already-in-group';
			}
		} else {
			return 'fgae';
		}
	}

	/**
	 * Remove flag from group.
	 *
	 * @param string $flag_key  The Key we're removing from the group.
	 * @param string $group_key The group we're removing the flag from.
	 *
	 * @return string
	 */
	public function remove_flag_from_group( $flag_key, $group_key ) {

		$flag   = empty( $flag );
		$group  = self::get_group( $group_key, false, true );
		$groups = self::get_groups();

		if ( false !== $flag && false !== $group ) {
			$groups[ $group ]->remove_flag( $flag_key );
			self::save_groups( $groups );
			return 'fgd';
		} else {
			return 'fgde';
		}
	}

	/**
	 * Remove a flag group from the system.
	 *
	 * @param string $key The flag group's key.
	 *
	 * @return string Result code.
	 */
	public function delete_group( $key ) {

		$index  = self::get_group( $key, false, true );
		$groups = self::get_groups();

		if ( $index >= 0 ) {
			unset( $groups[ $index ] );
			self::save_groups( $groups );
			return 'gd';
		} else {
			return 0;
		}

	}

	/**
	 * Returns the array of current flag groups found in the system.
	 *
	 * @return array|mixed
	 */
	public function get_groups() {

		$key    = self::get_options_key() . 'groups';
		$groups = maybe_unserialize( get_option( $key ) );

		if ( gettype( $groups ) !== 'array' ) {
			$groups = [];
		}

		return $groups;
	}

	/**
	 * Retrieve the group object of a specified key.
	 *
	 * @param string $key The group key we're looking for.
	 * @param bool   $check Return either if it's a valid group or the group itself.
	 * @param bool   $pos Return the position of $key in the Groups list.
	 *
	 * @return \FeatureFlag\Group|bool.
	 */
	public function get_group( $key, $check = false, $pos = false ) {

		$group    = false;
		$groups   = $this->groups;
		$position = false;

		foreach ( $groups as $index => $struct ) {
			if ( $key === $struct->key ) {
				$position = $index;
				$group    = $struct;
				break;
			}
		}

		if ( $group ) {
			if ( $pos ) {
				return $position;
			} else {
				return ( $check ? true : $group );
			}
		} else {
			// No group so everything should return false.
			return false;
		}
	}

	/**
	 * Save groups to the WordPress Database.
	 *
	 * @param bool $groups The groups to save if none uses system's.
	 */
	public function save_groups( $groups = false ) {
		$key = self::get_options_key() . 'groups';

		if ( false === $groups ) {
			$groups = self::get_groups();
		}

		update_option( $key, maybe_serialize( $groups ) );
	}
}







