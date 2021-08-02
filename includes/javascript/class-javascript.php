<?php
/**
 * JavaScript Class
 *
 * Inserts feature flag object and a corresponding flagpole_flag_enabled function into the <head>.
 *
 * @package flagpole
 */

namespace Flagpole;

/**
 * Class Flagpole_JavaScript.
 *
 * @package FeatureFlags
 */
class JavaScript {
	/**
	 * Initialise filters.
	 */
	public function init() {
		add_action( 'wp_head', array( $this, 'print_flagpole_js' ) );
	}

	/**
	 * Reduces the full array of flagpole flag objects into an array of enabled array keys.
	 * Returns an empty array if the 'flagpole_flag_enabled' PHP function is not present.
	 *
	 * @param array $flag_list The defined flags.
	 * @return array The keys of enabled flags.
	 */
	private function enabled_flag_filter( array $flag_list ) {
		$filtered_list = array();

		foreach ( $flag_list as $flag ) {
			if ( flagpole_flag_enabled( $flag->key ) ) {
				$filtered_list[] = $flag->key;
			}
		}

		return $filtered_list;
	}

	/**
	 * Prints a flagpole_flag_enabled function.
	 * The function returns 'true' if the flag is in our list of enabled flags.
	 * Otherwise, it returns false.
	 *
	 * @return void
	 */
	public function print_flagpole_js() {
		$available_flags = self::enabled_flag_filter( Flagpole::init()->get_flags() );
		?>
		<script>
			var flagpole_flag_enabled = function( ff ) {
				const flist = <?= wp_json_encode( $available_flags ); ?>;
				return flist.indexOf( ff ) !== -1;
			}
		</script>
		<?php
	}
}
