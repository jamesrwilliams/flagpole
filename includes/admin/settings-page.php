<?php
/**
 * Settings page that is used to render admin UI.
 *
 * @package feature-flags
 */

use FeatureFlags\FeatureFlags;

// Settings page.
add_action( 'admin_menu', function () {

	add_submenu_page( 'tools.php', 'Feature Flags', 'Feature Flags', 'edit_posts', 'feature-flags', function () {

		$available_flags = FeatureFlags::init()->get_flags();
		$enforced_flags  = FeatureFlags::init()->get_flags( true );

		?>

		<div class="wrap">

			<h1>Feature Flags</h1>

			<hr />

			<p>Feature flags or toggles allow features to easily be enabled for users to test in a more realistic environment.</p>

			<div class="notice-container"></div>

			<?php if ( $available_flags ) { ?>

				<h2>Available feature flags</h2>

				<table class="widefat">
					<thead>
					<tr>
						<th class="row-title">Feature</th>
						<th>Key</th>
						<th>Description</th>
						<th>Queryable</th>
						<th>Visibility</th>
						<th>&nbsp;</th>
					</tr>
					</thead>
					<tbody>

						<?php foreach ( $available_flags as $key => $flag ) { ?>

							<?php $enabled = is_enabled( $flag->get_key( false ) ); ?>

							<tr class="<?php echo( 0 === $key % 2 ? 'alternate' : null ); ?>">
								<td class="row-title"><span
										class="status-marker <?php echo( $enabled ? 'status-marker-enabled' : null ); ?>"
										title="<?php $flag->get_name(); ?> is currently <?php echo ( $enabled ? 'enabled' : 'disabled' ); ?>."></span><?php $flag->get_name(); ?>
								</td>
								<td>
									<pre><?php $flag->get_key(); ?></pre>
								</td>
								<td><?php $flag->get_description(); ?></td>
								<td><?php $flag->is_queryable( true ); ?></td>
								<td><?php $flag->is_private( true ); ?></td>
								<td>
									<?php

									if ( $enabled ) {
										submit_button( 'Disable preview', 'small', 'featureFlags-disable', false, [
											'data-action'          => 'toggleFeatureFlag',
											'data-status' => 'enabled',
										] );
									} else {
										submit_button( 'Enable preview', 'small', 'featureFlags-enable', false, [
											'data-action'          => 'toggleFeatureFlag',
											'data-status' => 'disabled',
										] );
									}

									?>

									<input type="submit" name="featureFlags-enable" id="featureFlags-enable" class="button button-small" value="Publish" data-action="toggleFeatureFlag" data-status="disabled">

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

		</div>
		<?php
	} );
} );
