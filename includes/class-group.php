<?php

/**
 * Group Class
 *
 * Used for creating feature flags.
 *
 * @package   wp-feature-flags
 * @author    James Williams <james@jamesrwilliams.ca>
 * @link      https://github.com/jamesrwilliams/wp-feature-flags
 * @copyright 2019 James Williams
 */

namespace FeatureFlag;

use FeatureFlags\FeatureFlags;

/**
 * Class Group
 *
 * @package FeatureFlag
 */
class Group {

	public $key;
	public $name;
	public $flags;

	public function __construct( $_key, $_name, $_flags = [] ) {

		$this->name  = ( $_name ? $_name : '' );
		$this->key   = $_key;
		$this->flags = $_flags;

	}

	/**
	 * @return mixed
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function get_flags() {
		return $this->flags;
	}

	/**
	 * @param $flag
	 */
	public function add_flag( $flag ) {

		$result = FeatureFlags::init()->find_flag( $flag );

		if ( $result ) {
			$this->flags[] = $flag;
		}
	}
}
