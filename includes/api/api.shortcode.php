<?php
/**
 * A home for all our short codes.
 *
 * @package wp-feature-flags
 */

use Flagpole\Flagpole;

/**
 * Register the wp-feature-flags debug short code.
 *
 * @param array $atts Array of arguments for the short code.
 *
 * @return string
 */
function flagpole_shortcode_debug_flags( $atts ) {
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
			$valid_keys[] = Flagpole::init()->find_flag( $key );
		}
		$keys = array_filter( $valid_keys );
	} else {
		$keys = Flagpole::init()->get_flags( $args['enforced'] );
	}

	if(!$keys) {
		return '<div class="ff-debug"><p>No flags found in current theme.</p></div>';
	}

	foreach ( $keys as $key ) {
		$status = ( $key->is_enabled() ? 'Enabled' : 'Disabled' );

		$html = $html . '<tr><td>' . $key->get_name( false ) . '</td><td><code>' . $key->get_key( false ) . '</code></td><td>' . $status . '</td><td>' . $key->is_enabled( true ) . '</td></tr>';
	}

	return '<div class="ff-debug">' . $title . $html . $footer . '</div>';
}

function flagpole_shortcode_debug_groups( $atts ) {

	$groups = Flagpole::init()->get_groups();

	$title  = '
<table class="table table-sm">
	<thead>
		<tr>
			<th>Group</th>
			<th>Key</th>
			<th>Flags</th>
			<th>Private</th>
		</tr>
	</thead><tbody>';
	$footer = '</tbody></table>';

	$html = '';

	foreach ($groups as $group) {
		$html = $html . '<tr><td>' . $group->name . '</td><td><code>' . $group->key . '</code></td><td>' . renderFlagList($group->flags, true) . '</td><td>' . ( $group->private ? 'Private' : 'Public' ) . '</td></tr>';
	}

	return '<div class="ff-debug">' . $title . $html . $footer . '</div>';
}

function renderFlagList($items) {
	$return = '';
	if(!$items) {
		return '0';
	}
	forEach( $items as $item ) {
		$flag = Flagpole::init()->find_flag($item);
		$return = $return . '<span style="display: inline-block; margin-right: 3px;' . ( $flag->is_enabled(false) ? 'font-weight: bold;' : '' ) . '">' . '<code title="' . $flag->get_name(false) . '">' . $flag->get_key(false) . '</code></span>';
	}
	return $return;
}

