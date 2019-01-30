<?php
/**
 * Settings page that is used to render admin UI.
 *
 * @package wp-feature-flags
 */

use FeatureFlags\FeatureFlags;

// Settings page.
add_action( 'admin_menu', function () {

	add_submenu_page( 'tools.php', 'Feature Flags', 'Feature Flags', 'edit_posts', 'feature-flags',
		function () {

			if ( isset( $_GET['error'] ) ) {
				echo '<h1>Error code 1</h1>';
			}

			$active_tab      = isset( $_GET['tab'] ) ? $_GET['tab'] : 'flags';
			$available_flags = FeatureFlags::init()->get_flags();
			$enforced_flags  = FeatureFlags::init()->get_flags( true );

			?>

			<?php if ( ! $enforced_flags && ! $available_flags ) { ?>

				<div class="notice notice-success is-dismissible">
					<p><strong>Heads Up!</strong> No feature flags have been detected in your theme.</p>
				</div>

				<p>Feature flags or toggles allow features to easily be enabled for users to test in a more realistic environment.</p>

				<p>We've got no flags so time to show a tutorial on how to add flags, maybe a link to the README etc.</p>

			<?php } else { ?>

			<div class="wrap">

				<h1>Feature Flags</h1>

				<h2 class="nav-tab-wrapper">
					<a href="?page=feature-flags&tab=flags" class="nav-tab <?php echo wp_kses_post( 'flags' === $active_tab ? 'nav-tab-active' : '' ); ?>">Feature Flags</a>
				</h2>

				<div class="notice-container"></div>

				<?php

				include_once plugin_dir_path( __FILE__ ) . '/settings-page/settings-page-' . $active_tab . '.php';

				?>

			<?php } ?>

		</div>
			<?php
		}
	);
}
);
