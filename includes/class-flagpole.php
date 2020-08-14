<?php
/**
 * FeatureFlag Class
 *
 * Used for creating feature flags.
 *
 * @package   flagpole
 * @author    James Williams <james@jamesrwilliams.ca>
 * @link      https://github.com/jamesrwilliams/wp-feature-flags
 * @copyright 2019 James Williams
 */

namespace Flagpole;

require_once 'class-flag.php';
require_once 'class-group.php';

use Flagpole\Flag;
use Flagpole\Group;

/**
 * Class FeatureFlags
 *
 * @package FeatureFlags
 */
class Flagpole {

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
	private static $meta_prefix = 'flagpole_';

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
		self::load_groups();
	}

	/**
	 * Load the groups data from the DB.
	 */
	private function load_groups() {
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
	 * @param string $key_key The flag key we're looking for.
	 * @param bool   $check Return either if it's a valid flag or the flag itself.
	 *
	 * @return \FeatureFlag\Flag|bool.
	 */
	public function find_flag( $key_key, $check = false ) {
		$flag  = false;
		$flags = $this->flags;

		foreach ( $flags as $struct ) {
			if ( $key_key === $struct->key ) {
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
	 * @param string  $flag_key The key of the flag we're looking for.
	 * @param boolean $reason Option to return reason why a flag is enabled.
	 *
	 * @return boolean Is the flag enabled or not.
	 */
	public function is_enabled( $flag_key, $reason = false ) {
		$flag = $this->find_flag( $flag_key );

		if ( $flag ) {
			if ( $flag->is_published() ) {
				return ( $reason ? 'Published' : true );
			} elseif ( $flag->get_enforced() ) {
				return ( $reason ? 'Enforced' : true );
			} else {
				if ( self::check_query_string( $flag_key ) ) {
					return ( $reason ? 'Group query string' : true );
				} elseif ( flagpole_user_enabled( $flag_key ) ) {
					return ( $reason ? 'User preview' : true );
				} elseif ( self::user_enabled_key_via_group( $flag_key ) ) {
					return ( $reason ? 'User preview with group' : true );
				} else {
					return ( $reason ? '' : false );
				}
			}
		} else {
			return ( $reason ? 'Not configured' : false );
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
	 * @param string $flag_key The feature key we're checking.
	 *
	 * @return bool
	 */
	public function has_user_enabled_flag( $flag_key ) {
		$user_id  = get_current_user_id();
		$response = false;

		if ( $user_id ) {

			// We have a user.
			$user_settings = self::get_user( $user_id, self::$meta_prefix, true );

			// Other.
			$response = ( isset( $user_settings[ $flag_key ] ) ? $user_settings[ $flag_key ] : false );
		}

		return $response;
	}

	/**
	 * Check if a user has enabled a flag via a group.
	 *
	 * @param string $flag_key The key we're looking for.
	 *
	 * @return bool
	 */
	public function user_enabled_key_via_group( $flag_key ) {

		/**
		 * Is the flag_key in any of the users current groups?
		 */

		$meta_key = self::get_options_key() . 'groups';
		$user_id  = get_current_user_id();
		$response = false;

		if ( $user_id ) {

			// We have a user.
			$groups = self::get_user( $user_id, $meta_key, true );

			if ( ! is_array( $groups ) ) {
				return $response;
			}

			foreach ( $groups as $group => $index ) {
				$group_obj = self::get_group( $group );

				if ( false !== $group_obj ) {
					$result = $group_obj->has_flag( $flag_key );

					if ( false !== $result ) {
						$response = ( $group_obj->in_preview() ? true : false );
						break;
					}
				}
			}
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
		$group = self::get_group($group_key);
		if($group) {
			return self::get_group( $group_key )->private;
		} else {
			return false;
		}

	}

	/**
	 * Toggle the feature for the current user.
	 *
	 * @param string $flag_key The feature key we're checking.
	 *
	 * @return void
	 */
	public function toggle_feature_preview( $flag_key ) {
		$user_id = get_current_user_id();

		if ( $user_id ) {
			$user_settings = self::get_user( $user_id, self::$meta_prefix, true );

			$enabled = ( $user_settings ?: [] );

			if ( $enabled[ $flag_key ] ) {
				$enabled[ $flag_key ] = ! $enabled[ $flag_key ];
			} else {
				$enabled[ $flag_key ] = true;
			}

			self::update_user( $user_id, self::$meta_prefix, $enabled );
		}
	}

	/**
	 * Toggle the preview status of a flag for the current user.
	 *
	 * @param string $group_key The key for the group we're toggling.
	 */
	public function toggle_group_preview( $group_key ) {
		$user_id  = get_current_user_id();
		$meta_key = self::$meta_prefix . 'groups';

		if ( $user_id ) {
			$user_settings = self::get_user( $user_id, $meta_key, true );

			$enabled = ( $user_settings ?: [] );

			if ( $enabled[ $group_key ] ) {
				$enabled[ $group_key ] = ! $enabled[ $group_key ];
			} else {
				$enabled[ $group_key ] = true;
			}

			self::update_user( $user_id, $meta_key, $enabled );
		}
	}

	/**
	 * Toggles a feature for publication.
	 *
	 * @param string $flag_key The key of the feature to toggle publication status.
	 *
	 * @return void|string JSON response if error.
	 */
	public function toggle_feature_publication( $flag_key ) {
		$meta_key        = self::$meta_prefix . 'flags';
		$published_flags = maybe_unserialize( get_option( $meta_key ) );
		$options_type    = gettype( $published_flags );

		if ( 'array' !== $options_type ) {
			$published_flags = [];
			add_option( $meta_key, maybe_serialize( $published_flags ) );
		}

		$found_in_options = array_search( $flag_key, $published_flags, true );

		if ( false === $found_in_options || - 1 === $found_in_options ) {
			if ( self::find_flag( $flag_key )->stable !== true ) {
				return wp_json_encode( 'This feature is unstable.' );
			} else {
				$published_flags[] = $flag_key;
			}
		} else {
			unset( $published_flags[ $found_in_options ] );
		}

		update_option( $meta_key, $published_flags, true );
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
	public function get_user( $user_id, $key, $single = true ) {
		if ( defined( 'WPCOM_VIP_CLIENT_MU_PLUGIN_DIR' ) ) {
			/**
			 * On VIP GO sites debug logs get filled with deprecation notices when get_user_attribute() is used.
			 * Constant WPCOM_VIP_CLIENT_MU_PLUGIN_DIR is only defined on VIP GO platform as per docs available at
			 * https://wpvip.com/documentation/vip-go/managing-plugins/#installing-to-the-client-mu-plugins%c2%a0directory
			 *
			 * phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_get_user_meta
			 */
			return get_user_meta( $user_id, $key, $single );
		} elseif ( function_exists( 'get_user_attribute' ) ) {
			/**
			 * On wordpress.com we must use get_user_attribute() as per
			 * https://lobby.vip.wordpress.com/wordpress-com-documentation/user_meta-vs-user_attributes/
			 */
			return get_user_attribute( $user_id, $key );
		} else {
			/**
			 * On self-hosted/wordpress.org sites we can use get_user_meta()
			 *
			 * phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_get_user_meta
			 */
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
		if ( defined( 'WPCOM_VIP_CLIENT_MU_PLUGIN_DIR' ) ) {
			/**
			 * On VIP GO sites debug logs get filled with deprecation notices when update_user_attribute() is used.
			 * Constant WPCOM_VIP_CLIENT_MU_PLUGIN_DIR is only defined on VIP GO platform as per docs available at
			 * https://wpvip.com/documentation/vip-go/managing-plugins/#installing-to-the-client-mu-plugins%c2%a0directory
			 *
			 * phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_update_user_meta
			 */
			return update_user_meta( $user_id, $meta_key, $meta_value, $prev_value );
		} elseif ( function_exists( 'update_user_attribute' ) ) {
			/**
			 * On wordpress.com we must use update_user_attribute() as per
			 * https://lobby.vip.wordpress.com/wordpress-com-documentation/user_meta-vs-user_attributes/
			 */
			return update_user_attribute( $user_id, $meta_key, $meta_value );
		} else {
			/**
			 * On self-hosted/wordpress.org sites we can use update_user_meta()
			 *
			 * phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_update_user_meta
			 */
			return update_user_meta( $user_id, $meta_key, $meta_value, $prev_value );
		}
	}

	/**
	 * Check if a query argument has been passed to enable a flag manually.
	 * Also validates it's publicly queryable.
	 *
	 * @param string $flag_key The key of the flag we're aiming to match.
	 *
	 * @return bool Is there a query string for this flag currently?
	 */
	public function check_query_string( $flag_key ) {
		$query = flagpole_find_query_string();

		if ( ! empty( $query ) && $query ) {
			$group = $this->get_group( $query );

			if ( false !== $group ) {
				return $group->has_flag( $flag_key );
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Register a new Flag group.
	 *
	 * @param string $group_key string The Group key.
	 * @param string $name string The Group name to create.
	 * @param string $description string Optional description for the group.
	 * @param bool   $private Is this group publicly accessible.
	 *
	 * @return string Error response message key.
	 */
	public function create_group( $group_key, $name, $description = '', $private = false ) {
		$sanitised_key = sanitize_title( $group_key );

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
	 * @param string $group_key The feature group's key.
	 * @param array  $args The parameters to update for $key.
	 */
	public function update_group( $group_key, $args = [] ) {
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
		$group_index = self::get_group( $group_key, false, true );
		$groups      = self::get_groups();

		if ( false !== $flag_key && false !== $group_index ) {

			// Get group index and remove it from the groups array.
			$groups[ $group_index ]->remove_flag( $flag_key );
			self::save_groups( $groups );

			return 'fgd';
		} else {
			return 'fgde';
		}
	}

	/**
	 * Remove a flag group from the system.
	 *
	 * @param string $group_key The flag group's key.
	 *
	 * @return string Result code.
	 */
	public function delete_group( $group_key ) {
		$index  = self::get_group( $group_key, false, true );
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
	 * @param string $group_key The group key we're looking for.
	 * @param bool   $check Return either if it's a valid group or the group itself.
	 * @param bool   $pos Return the position of $key in the Groups list.
	 *
	 * @return \FeatureFlag\Group|bool.
	 */
	public function get_group( $group_key, $check = false, $pos = false ) {
		$group    = false;
		$groups   = $this->groups;
		$position = false;

		foreach ( $groups as $index => $struct ) {
			if ( $group_key === $struct->key ) {
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







