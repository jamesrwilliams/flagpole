<?php
/**
 * CLI Class
 *
 * Used for managing WP-CLI integration.
 *
 * @package   flagpole
 */

namespace Flagpole;

use Flagpole\Flagpole;
use Flagpole\Flag;
use WP_CLI;


/**
 * Class CLI
 *
 * @package FeatureFlag
 */
class CLI {
	private const FLAGPOLE_CLI_PREFIX = 'flagpole ';

	/**
	 * Registers WP CLI commands.
	 */
	public function register() {
		WP_CLI::add_command( self::FLAGPOLE_CLI_PREFIX . 'list', [ $this, 'flag_list' ] );
		WP_CLI::add_command( self::FLAGPOLE_CLI_PREFIX . 'activate', [ $this, 'flag_on' ] );
		WP_CLI::add_command( self::FLAGPOLE_CLI_PREFIX . 'deactivate', [ $this, 'flag_off' ] );
		WP_CLI::add_command( self::FLAGPOLE_CLI_PREFIX . 'toggle', [ $this, 'flag_toggle' ] );
	}

	private function flag_filter_enabled( $flag_item ) {
		if ( ( gettype( $flag_item['enabled'] ) === 'boolean') && ( $flag_item['enabled'] === true ) || ( $flag_item['enabled'] === 'true' ) ) {
			return $flag_item;
		}
	}

	/**
	 * Encodes flagpole array data in a way that is easier to read when displayed.
	 * Specifically, we convert booleans to string equivalents and add 'enabled' data.
	 *
	 * @param array $data
	 * @return array
	 */
	private function flag_list_data_encode( $data, $bool_type = 'string' ) {
		$encoded_data = [];

		$true_val = 'true';
		$false_val = 'false';

		if ( $bool_type == 'boolean' ) {
			$true_val = true;
			$false_val = false;
		}

		foreach ( $data as $flag_key => $flag_value ) {
			$encoded_data[ $flag_value->get_key( false ) ] = [
				'key' => $flag_value->get_key( false ),
				'name' => $flag_value->get_name( false ),
				'description' => $flag_value->get_description( false ),
				'label' => $flag_value->get_label( false ),
				'activated' => $flag_value->is_enabled() ? $true_val : $false_val,
				'stable' => $flag_value->is_stable( false ) ? $true_val : $false_val,
				'enforced' => $flag_value->get_enforced() ? $true_val : $false_val,
				'private' => $false_val
			];
		}

		return $encoded_data;
	}

	/**
	 * Lists all registered flags.
	 *
	 * ## OPTIONS
	 *
	 * <type>
	 * : The type of table to display.
	 * default: table
	 * options:
	 * - csv
	 * - json
	 * - table
	 * - yaml
	 *
	 * ## EXAMPLES
	 *
	 *     wp flagpole list
	 *     wp flagpole list csv
	 *
	 */
	public function flag_list( $args ) {
		/**
		 * @todo switch to associated arguments only.
		 * @todo filter by group/label.
		 * @todo filter by on/off.
		 * @todo filter fields to return.
		 */

		// We don't support the 'ids' or 'count' types, as there's no real benefit to them here.
		$render_types = [
			'table',
			'csv',
			'json',
			'yaml',
		];

		$render_type = 'table';

		if ( $args !== [] and in_array( $args[0], $render_types, true ) ) {
			$render_type = $args[0];
		}

		$available_flags = Flagpole::init()->get_flags();

		$fields_to_display = [
			'key',
			'name',
			'description',
			'label',
			'stable',
			'activated',
			'enforced',
			'private',
		];

		$bool_type = 'boolean';

		if ( 'table' === $render_type ) {
			$bool_type = 'string';
		}

		$available_flags = $this->flag_list_data_encode( $available_flags, $bool_type );

		$enabled_flags = array_filter( $available_flags, [ $this, 'flag_filter_enabled' ] );

		WP_CLI::line( 'Flags: ' . count( $available_flags ) . ' registered. ' . count( $enabled_flags ) . ' enabled.' );
		WP_CLI::line();

		if ( count( $available_flags ) > 0 ) {
			WP_CLI\Utils\format_items( $render_type, $available_flags, $fields_to_display );
			WP_CLI::line();
		}
	}

	/**
	 * Activates any given flag or a comma separated list of flags.
	 * If any given flag is already active, its state is not changed.
	 *
	 * ## OPTIONS
	 *
	 * <flag(s)>
	 * : The key of the flag to activate. This can be a comma separated list for multiple flags.
	 *
	 * ## EXAMPLES
	 *
	 *     wp flagpole activate my-example-flag
	 *     wp flagpole activate my-example-flag,my-other-example-flag
	 *     wp flagpole on my-example-flag
	 *
	 * @alias on
	 */
	public function flag_on( $args ) {
		if ( $args == [] ) {
			WP_CLI::error( 'Flag not provided' );
			return;
		}

		if ( $args[0] ) {
			if ( strpos( $args[0], ',' ) > 0 ) {
				$flags = explode( ',', $args[0] );
			} else {
				$flags = [ $args[0] ];
			}

			foreach( $flags as $flag ) {
				if ( true === $this->flag_exists_and_has_state( $flag, true ) ) {
					Flagpole::init()->toggle_feature_publication( $flag );
					WP_CLI::success( $flag . ' activated.' );
				}
			}
			return;
		}
	}

	/**
	 * Deactivates any given flag or a comma separated list of flags.
	 * If any given flag is already deactivated, its state is not changed.
	 *
	 * ## OPTIONS
	 *
	 * <flag(s)>
	 * : The key of the flag to activate. This can be a comma separated list for multiple flags.
	 *
	 * ## EXAMPLES
	 *
	 *     wp flagpole deactivate my-example-flag
	 *     wp flagpole deactivate my-example-flag,my-other-example-flag
	 *     wp flagpole off my-example-flag
	 *
	 * @alias off
	 */
	public function flag_off( $args ) {
		if ( $args == [] ) {
			WP_CLI::error( 'Flag not provided' );
			return;
		}

		if ( $args[0] ) {
			if ( strpos( $args[0], ',' ) > 0 ) {
				$flags = explode( ',', $args[0] );
			} else {
				$flags = [ $args[0] ];
			}

			foreach( $flags as $flag ) {
				if ( true === $this->flag_exists_and_has_state( $flag, false ) ) {
					Flagpole::init()->toggle_feature_publication( $flag );
					WP_CLI::success( $flag . ' deactivated.' );
				}
			}
		}
	}

	/**
	 * Toggles any given flag or a comma separated list of flags.
	 *
	 * ## OPTIONS
	 *
	 * <flag(s)>
	 * : The key of the flag to toggle. This can be a comma separated list for multiple flags.
	 *
	 * ## EXAMPLES
	 *
	 *     wp flagpole toggle my-example-flag
	 *     wp flagpole toggle my-example-flag,my-other-example-flag
	 *
	 */
	public function flag_toggle( $args ) {
		if ( $args == [] ) {
			WP_CLI::error( 'Flag not provided' );
			return;
		}

		if ( $args[0] ) {
			if ( strpos( $args[0], ',' ) > 0 ) {
				$flags = explode( ',', $args[0] );
			} else {
				$flags = [ $args[0] ];
			}

			foreach( $flags as $flag ) {
				$query_flag = Flagpole::init()->find_flag( $flag );
				$is_currently_enabled = $query_flag->is_enabled();

				Flagpole::init()->toggle_feature_publication( $flag );
				if ( true === $is_currently_enabled ) {
					WP_CLI::success( $flag . ' deactivated.' );
				} else {
					WP_CLI::success( $flag . ' activated.' );
				}
			}
		}
	}

	/**
	 * Helper functions.
	 */

	/**
	 * Looks up a flag, checks if it exists and is of a given state.
	 *
	 * @param string $flag Name of the flag.
	 * @param boolean $activated Whether the flag is activated or deactivated.
	 * @return void
	 */
	function flag_exists_and_has_state ( $flag, $activated = true ) {
		$query_flag = Flagpole::init()->find_flag( $flag );

		if ( false === $query_flag ) {
			WP_CLI::error( $flag . ' is not a registered flag' );
			return false;
		}

		if ( true === $activated && true === $query_flag->is_enabled() ) {
			WP_CLI::success( $flag . ' is already active.' );
			return false;
		}

		if ( false === $activated && false === $query_flag->is_enabled() ) {
			WP_CLI::success( $flag . ' is already deactive.' );
			return false;
		}

		return true;
	}
}
