<?php
/**
 * Settings page partial for the Flags page.
 *
 * @package feature-flags
 */

?>

<?php if ( $available_flags ) { ?>

	<h2>Available feature flags</h2>

	<table class="widefat flags_table">
		<thead>
		<tr>
			<th class="row-title">Feature</th>
			<th>Key</th>
			<th>Description</th>
			<th>Visibility</th>
			<th colspan="2">Actions</th>
		</tr>
		</thead>
		<tbody>

		<?php foreach ( $available_flags as $key => $flag ) { ?>

			<?php $enabled = has_user_enabled( $flag->get_key( false ) ); ?>
			<?php $published = $flag->is_published(); ?>

			<tr class="<?php echo( 0 === $key % 2 ? null : 'alternate' ); ?>">
				<td class="title">
					<strong><?php wp_kses_post( $flag->get_name() ); ?></strong>
				</td>
				<td>
					<code><?php $flag->get_key(); ?></code>
				</td>
				<td><?php $flag->get_description(); ?></td>
				<td>
					<?php

					if ( $enabled ) {
						submit_button(
							'Disable preview',
							'small',
							'featureFlagsBtn_disable',
							false,
							[
								'class'       => 'action-btn',
								'data-action' => 'toggleFeatureFlag',
								'data-status' => 'enabled',
							]
						);
					} else {
						submit_button(
							'Enable preview',
							'primary small',
							'featureFlagsBtn_enable',
							false,
							[
								'class'       => 'action-btn',
								'data-action' => 'toggleFeatureFlag',
								'data-status' => 'disabled',
							]
						);
					}
					?>

				</td><td>
					<?php

					$stable       = $flag->is_stable( false );
					$button_style = ( $stable ? 'primary small' : 'small' );
					$button_text  = ( $published ? 'Unpublish' : 'Publish' );
					$button_name  = ( $published ? 'featureFlagsBtn_unpublish' : 'featureFlagsBtn_publish' );
					$other_args   = [
						'data-action' => 'togglePublishedFeature',
					];

					if ( ! $stable ) {
						$other_args['disabled'] = true;
						$button_text            = 'Disabled';
						$other_args['title']    = 'Feature is marked as unstable.';
					}

					submit_button(
						$button_text,
						$button_style,
						$button_name,
						false,
						$other_args
					);

					?>

				</td>
			</tr>

		<?php } ?>

		</tbody>
	</table>

<?php } ?>

<?php if ( $enforced_flags ) { ?>
	<h2>Enforced feature flags</h2>
	<p>Features listed below are currently configured to be <code>enforced</code> by default by the developers. These are flags that will likely be removed from the website source code soon.</p>

	<table class="widefat">
		<thead>
		<tr>
			<th class="row-title">Feature</th>
			<th>Key</th>
			<th>Description</th>
		</tr>
		</thead>
		<tbody>

		<?php foreach ( $enforced_flags as $key => $flag ) { ?>

			<tr class="<?php echo( 0 === $key % 2 ? 'alternate' : null ); ?>">
				<td class="row-title"><?php $flag->get_name(); ?></td>
				<td>
					<pre><?php $flag->get_key(); ?></pre>
				</td>
				<td><?php $flag->get_description(); ?></td>
			</tr>

		<?php } ?>

		</tbody>
	</table>

<?php } ?>
