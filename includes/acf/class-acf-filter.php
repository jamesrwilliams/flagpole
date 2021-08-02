<?php
/**
 * Class to add feature flags defined with Flagpole as a filter option in ACF.
 * Documentation for this functionality available here: https://www.advancedcustomfields.com/resources/custom-location-rules/
 *
 * @package Peake\Plugins
 */

namespace Flagpole;

use ACF_Location;
use Flagpole\Flagpole;

/**
 * Class ACF_Filter
 *
 * @package FeatureFlags
 */
class ACF_Filter extends ACF_Location {
	// Type hints must match the original source exactly, so PHPCS checks have been disabled but docblocks are accurate.
	// phpcs:disable NeutronStandard.Functions.TypeHint.NoArgumentType
	// phpcs:disable NeutronStandard.Functions.TypeHint.NoReturnType
	// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

	/**
	 * Sets the base values for the location filter.
	 *
	 * @return void
	 */
	public function initialize() {
		$this->name     = 'feature-flags';
		$this->label    = __( 'Feature flags', 'flagpole' );
		$this->category = 'forms';
	}

	/**
	 * Gets the list of feature flags as an ID => printable name pair.
	 *
	 * @param array $rule Information on the current rule (value, parameter, operator etc.).
	 * @return array List of all flag IDs and names.
	 */
	public function get_values( $rule ) {
		$flagpole_flags  = Flagpole::init()->get_flags();
		$flagpole_values = array();

		foreach ( $flagpole_flags as $flagpole_flag ) {
			$flagpole_values[ $flagpole_flag->key ] = $flagpole_flag->name;
		}

		return $flagpole_values;
	}

	/**
	 * Returns an array of operators.
	 *
	 * @param  array $rule A location rule.
	 * @return array
	 */
	public static function get_operators( $rule ) {
		return array(
			'==' => __( 'is enabled', 'flagpole' ),
			'!=' => __( 'is not enabled', 'flagpole' ),
		);
	}

	/**
	 * Returns true or false depending on whether or not the feature flag is enabled and whether our operator is '==' or '!='.
	 *
	 * @param array $rule Parameter info, including the operator and feature flag ID value.
	 * @param array $screen Current page info (post type, ID, language).
	 * @param array $field_group Field group info (field group name, rules, position etc.).
	 * @return boolean Whether our parameters have been met.
	 */
	public function match( $rule, $screen, $field_group ) {
		if ( '==' === $rule['operator'] ) {
			return flagpole_flag_enabled( $rule['value'] );
		}

		if ( '=!' === $rule['operator'] ) {
			return ! flagpole_flag_enabled( $rule['value'] );
		}

		return false;
	}

	// phpcs:enable NeutronStandard.Functions.TypeHint.NoArgumentType
	// phpcs:enable NeutronStandard.Functions.TypeHint.NoReturnType
	// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
}
