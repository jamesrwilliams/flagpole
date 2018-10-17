<?php

/**
 * Flag Class
 *
 * Used for creating feature flags.
 *
 * @package   feature-flags
 * @author    James Williams <james@jamesrwilliams.co.uk>
 * @link      https://github.com/jamesrwilliams/feature-flags
 * @copyright 2018 James Williams
 */

namespace FeatureFlag;

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
	 * Flag constructor.
	 *
	 * @param string $_key The Key for the feature.
	 * @param string $_name The human readable name for the flag.
	 * @param bool   $_enforced Is the key enforced.
	 * @param string $_description The description to be shown in the admin about the field.
	 * @param bool   $_queryable Can you access the flag with a query string?
	 */
	public function __construct( $_key, $_name, $_enforced, $_description, $_queryable ) {

		$this->enforced    = $_enforced;
		$this->name        = ( $_name ? $_name : '' );
		$this->key         = $_key;
		$this->description = $_description;
		$this->queryable   = $_queryable;

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
	 * Retrieve the status of a flag's enforced state.
	 *
	 * @return bool The status of if a flag is enforced or not.
	 */
	public function get_enforced() {

		return $this->enforced;

	}

}
