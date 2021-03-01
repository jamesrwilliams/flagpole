<?php
/**
 * Flag Class
 *
 * Used for creating feature flags.
 *
 * @package   flagpole
 * @author    James Williams <james@jamesrwilliams.ca>
 * @link      https://github.com/jamesrwilliams/wp-feature-flags
 * @copyright 2019 James Williams
 */

namespace Flagpole;

use Flagpole\Flagpole;

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
	 * A label for the flag to belong to.
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Flag constructor.
	 *
	 * @param string $_key The Key for the feature.
	 * @param string $_name The human readable name for the flag.
	 * @param bool   $_enforced Is the key enforced.
	 * @param string $_description The description to be shown in the admin about the field.
	 * @param bool   $_stable Allow this flag to be published or not.
	 * @param string $_label The subsection of the feature flag list. Defaults to 'all'.
	 */
	public function __construct( $_key, $_name, $_enforced, $_description, $_stable, $_label ) {
		$this->enforced    = $_enforced;
		$this->name        = ( $_name ? $_name : '' );
		$this->key         = $_key;
		$this->description = $_description;
		$this->stable      = $_stable;
		$this->label       = ( $_label ? $_label : 'All' );
	}

	/**
	 * Display or retrieve the flag key.
	 *
	 * @param boolean $echo Echo or return the response.
	 * @return null|string Current flag key if $echo is false.
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
	 * @return null|string Current flag key if $echo is false.
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
	 * @return null|string Current flag key if $echo is false.
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
	 * Display or retrieve the flag label.
	 *
	 * @param boolean $echo Echo or return the response.
	 * @return null|string Current label text if $echo is false.
	 */
	public function get_label( $echo = true ) {
		$label = $this->label;

		if ( $echo ) {
			echo wp_kses_post( $label );
		} else {
			return $label;
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
		$meta_key = Flagpole::init()->get_options_key() . 'flags';

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
		return Flagpole::init()->is_enabled( $this->key, $reason );
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
