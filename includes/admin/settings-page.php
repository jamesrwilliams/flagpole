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

		if ( isset( $_GET['error'] ) ) {
			echo "<h1>Error code 1</h1>";
		}

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
							<?php $published = false; ?>

							<tr class="<?php echo( 0 === $key % 2 ? 'alternate' : null ); ?>">
								<td class="row-title"><span
										class="status-marker <?php echo( $enabled ? 'status-marker-enabled' : null ); ?>"
										title="<?php echo $flag->get_name(); ?> is currently <?php echo ( $enabled ? 'enabled' : 'disabled' ); ?>."></span><?php $flag->get_name(); ?>
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
											'small',
											'featureFlagsBtn_enable',
											false,
											[
												'class'       => 'action-btn',
												'data-action' => 'toggleFeatureFlag',
												'data-status' => 'disabled',
											]
										);
									}

									if ( $published ) {
										submit_button(
											'Unpublish',
											'small',
											'featureFlagsBtn_unpublish',
											false,
											[
												'class'       => 'action-btn',
												'data-action' => 'togglePublishedFeature',
												'data-status' => 'enabled',
											]
										);
									} else {
										submit_button(
											'Publish',
											'small',
											'featureFlagsBtn_publish',
											false,
											[
												'data-action' => 'togglePublishedFeature',
												'data-status' => 'disabled',
											]
										);
									}

									?>

								</td>
							</tr>

						<?php } ?>

					</tbody>
				</table>

			<?php } ?>

			<h2>Published Flags</h2>
			<p>Here is a debug output of whats currently in our site options table for published features:</p>

			<?php $output = get_option( 'enabledFlags' ); ?>

			<pre><code><?php var_dump( $output ); ?></code></pre>

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
