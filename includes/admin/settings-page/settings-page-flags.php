<?php
/**
 * Settings page partial for the Flags page.
 *
 * @package flagpole
 */

?>

<?php if ( $flagpole_available_flags ) { ?>

	<h2>Available flags</h2>

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

		<?php foreach ( $flagpole_available_flags as $flagpole_key => $flagpole_flag ) { ?>

			<?php $flagpole_enabled = flagpole_user_enabled( $flagpole_flag->get_key( false ) ); ?>
			<?php $flagpole_published = $flagpole_flag->is_published(); ?>

			<tr class="<?php echo( 0 === $flagpole_key % 2 ? null : 'alternate' ); ?>">
				<td class="title">
					<strong><?php wp_kses_post( $flagpole_flag->get_name() ); ?></strong>
				</td>
				<td>
					<code><?php $flagpole_flag->get_key(); ?></code>
				</td>
				<td><?php $flagpole_flag->get_description(); ?></td>
				<td>
					<?php

					if ( $flagpole_enabled ) {
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

					$flagpole_stable       = $flagpole_flag->is_stable( false );
					$flagpole_button_style = ( $flagpole_stable ? 'primary small' : 'small' );
					$flagpole_button_text  = ( $flagpole_published ? 'Unpublish' : 'Publish' );
					$flagpole_button_name  = ( $flagpole_published ? 'featureFlagsBtn_unpublish' : 'featureFlagsBtn_publish' );
					$flagpole_other_args   = [
						'data-action' => 'togglePublishedFeature',
					];

					if ( ! $flagpole_stable ) {
						$flagpole_other_args['disabled'] = true;
						$flagpole_button_text            = 'Disabled';
						$flagpole_other_args['title']    = 'Feature is marked as unstable.';
					}

					submit_button(
						$flagpole_button_text,
						$flagpole_button_style,
						$flagpole_button_name,
						false,
						$flagpole_other_args
					);

					?>

				</td>
			</tr>

		<?php } ?>

		</tbody>
	</table>

<?php } ?>

<?php if ( $flagpole_enforced_flags ) { ?>
	<h2>Enforced flags</h2>
	<p>Flags that are listed below are currently configured to be <code>enforced</code> by default by the developers. These are flags that will likely be removed from the website source code soon.</p>

	<table class="widefat">
		<thead>
		<tr>
			<th class="row-title">Feature</th>
			<th>Key</th>
			<th>Description</th>
		</tr>
		</thead>
		<tbody>

		<?php foreach ( $flagpole_enforced_flags as $flagpole_key => $flagpole_flag ) { ?>

			<tr class="<?php echo( 0 === $flagpole_key % 2 ? 'alternate' : null ); ?>">
				<td class="row-title"><?php $flagpole_flag->get_name(); ?></td>
				<td>
					<code><?php $flagpole_flag->get_key(); ?></code>
				</td>
				<td><?php $flagpole_flag->get_description(); ?></td>
			</tr>

		<?php } ?>

		</tbody>
	</table>

<?php } ?>
