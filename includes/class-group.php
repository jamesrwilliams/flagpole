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
	public $description;
	public $flags;

	public function __construct( $_key, $_name, $_description = '', $_flags = [] ) {

		$this->name        = ( $_name ? $_name : '' );
		$this->key         = $_key;
		$this->flags       = $_flags;
		$this->description = $_description;

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
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @return array
	 */
	public function get_flags() {
		return $this->flags;
	}
}
