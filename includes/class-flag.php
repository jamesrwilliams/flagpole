<?php
/**
 * Flag Class
 *
 * Used for creating feature flags.
 *
 * @package   wp-feature-flags
 * @author    James Williams <james@jamesrwilliams.ca>
 * @link      https://github.com/jamesrwilliams/wp-feature-flags
 * @copyright 2019 James Williams
 */

use FeatureFlags\FeatureFlags;

/**
 * Class Flag
 *
 * @package FeatureFlag
 */
class Flag {

	/**
	 * Flags can be enforced. When they are they bypass
	 *
	 * @var bool
	 */
	public $enforced;

	/**
	 * The human readable name of a flag.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The unique feature flag key associated with a feature.
	 *
	 * @var string
	 */
	public $key;

	/**
	 * The description of the feature.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Enable if the flag can be enabled with a query string.
	 *
	 * @var bool
	 */
	public $queryable;

	/**
	 * Does the flag require users to be logged in.
	 *
	 * @var bool
	 */
	public $private;

	/**
	 * Boolean whether or not the feature can be published.
	 *
	 * @var bool
	 */
	public $stable;

	/**
	 * Flag constructor.
	 *
	 * @param string $_key The Key for the feature.
	 * @param string $_name The human readable name for the flag.
	 * @param bool   $_enforced Is the key enforced.
	 * @param string $_description The description to be shown in the admin about the field.
	 * @param bool   $_queryable Set whether or not you can you access the flag with a query string.
	 * @param bool   $_private Allow this flag to be enabled without logging in.
	 * @param bool   $_stable Allow this flag to be published or not.
	 */
	public function __construct( $_key, $_name, $_enforced, $_description, $_queryable, $_private, $_stable ) {

		$this->enforced    = $_enforced;
		$this->name        = ( $_name ? $_name : '' );
		$this->key         = $_key;
		$this->description = $_description;
		$this->queryable   = $_queryable;
		$this->private     = $_private;
		$this->stable      = $_stable;

	}

	/**
	 * Display or retrieve the flag key.
	 *
	 * @param boolean $echo Echo or return the response.
	 * @return string|void Current flag key if $echo is false.
	 */
	public function get_key( $echo = true ) {

		$key = $this->key;

		if ( $echo ) {
			echo wp_kses_post( $key );
		} else {
			return $key;
		}

	}

	/**
	 * Display or retrieve the flag name.
	 *
	 * @param boolean $echo Echo or return the response.
	 * @return string|void Current flag key if $echo is false.
	 */
	public function get_name( $echo = true ) {

		$name = $this->name;

		if ( $echo ) {
			echo wp_kses_post( $name );
		} else {
			return $name;
		}

	}

	/**
	 * Display or retrieve the flag name.
	 *
	 * @param boolean $echo Echo or return the response.
	 * @return string|void Current flag key if $echo is false.
	 */
	public function get_description( $echo = true ) {

		$description = $this->description;

		if ( $echo ) {

			echo wp_kses_post( $description );

		} else {

			return $description;

		}

	}

	/**
	 * Check if a flag is publicly queryable.
	 *
	 * @param bool $echo Echo or return the response.
	 * @return string|void Yes or no string if echo is true.
	 */
	public function is_queryable( $echo = true ) {

		$queryable = $this->queryable;

		if ( $echo ) {

			echo wp_kses_post( $queryable ? 'Yes' : 'No' );

		} else {

			return $queryable;

		}

	}

	/**
	 * Check if a flag is publicly queryable.
	 *
	 * @param bool $echo Echo or return the response.
	 * @return string|void Yes or no string if echo is true.
	 */
	public function is_private( $echo = true ) {

		$private = $this->private;

		if ( $echo ) {

			echo wp_kses_post( $private ? 'Private' : 'Public' );

		} else {

			return $private;

		}

	}

	/**
	 * Check to see if a variable is stable or not.
	 *
	 * @param bool $echo Echo or return the response.
	 * @return null|string Yes or no string if echo is true.
	 */
	public function is_stable( $echo = true ) {
		$stable = $this->stable;

		if ( $echo ) {

			echo wp_kses_post( $stable ? 'Stable' : 'Unstable' );

		} else {

			return $stable;

		}
	}

	/**
	 * Check if this flag is published globally or not.
	 *
	 * @return bool Is the flag published?
	 */
	public function is_published() {

		$meta_key = FeatureFlags::init()->get_options_key();

		/* Get options */
		$published_flags = maybe_unserialize( get_option( $meta_key ) );
		$options_type    = gettype( $published_flags );

		if ( 'array' !== $options_type ) {
			$published_flags = [];
			add_option( $meta_key, maybe_serialize( $published_flags ) );

		}

		$found_in_options = array_search( $this->key, $published_flags, true );

		if ( false === $found_in_options || - 1 === $found_in_options ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Check if a flag is currently enabled.
	 *
	 * @param bool $reason Do we want the reason why the flag is enabled or just the status.
	 *
	 * @return bool
	 */
	public function is_enabled( $reason = false ) {

		return FeatureFlags::init()->is_enabled( $this->key, $reason );

	}

	/**
	 * Retrieve the status of a flag's enforced state.
	 *
	 * @return bool The status of if a flag is enforced or not.
	 */
	public function get_enforced() {

		return $this->enforced;

	}

}
