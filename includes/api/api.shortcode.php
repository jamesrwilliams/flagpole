<?php
/**
 * A home for all our short codes.
 *
 * @package wp-feature-flags
 */

use FeatureFlags\FeatureFlags;

/**
 * Register the wp-feature-flags debug short code.
 *
 * @param array $atts Array of arguments for the short code.
 *
 * @return string
 */
function shortcode_debug( $atts ) {
	$args = shortcode_atts(
		[
			'flag'     => 'all',
			'enforced' => false,
		],
		$atts
	);

	$title  = '
<table class="table table-sm">
	<thead>
		<tr>
			<th>Flag</th>
			<th>Key</th>
			<th>Status</th>
			<th>Reason</th>
		</tr>
	</thead><tbody>';
	$footer = '</tbody></table>';

	$html = '';

	if ( 'all' !== $args['flag'] ) {

		$string = str_replace( ' ', '', $args['flag'] );
		$keys   = explode( ',', $string );

		// TODO: Check the keys provided are valid before proceeding.
		$valid_keys = [];
		foreach ( $keys as $key ) {
			$valid_keys[] = FeatureFlags::init()->find_flag( $key );
		}
		$keys = array_filter( $valid_keys );

	} else {
		$keys = FeatureFlags::init()->get_flags( $args['enforced'] );
	}

	foreach ( $keys as $key ) {

		$status = ( $key->is_enabled() ? 'Enabled' : 'Disabled' );

		$html = $html . '<tr><td>' . $key->get_name( false ) . '</td><td><code>' . $key->get_key( false ) . '</code></td><td>' . $status . '</td><td>' . $key->is_enabled( true ) . '</td></tr>';
	}

	return $title . $html . $footer;
}
