<?php
/**
 * Settings page that is used to render admin UI.
 *
 * @package wp-feature-flags
 */

use Flagpole\Flagpole;

// Settings page.
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'tools.php',
			'Flagpole',
			'Flagpole',
			'edit_posts',
			'flagpole',
			function () {

				// TODO Add nonce to admin pages for tab and flags.
				$flagpole_active_tab      = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'flags';
				$flagpole_available_flags = Flagpole::init()->get_flags();
				$flagpole_enforced_flags  = Flagpole::init()->get_flags( true );

				?>

					<?php if ( ! $flagpole_enforced_flags && ! $flagpole_available_flags ) { ?>

						<div class="notice notice-success is-dismissible">
							<p><strong>Heads Up!</strong> No feature flags have been detected in your theme.</p>
						</div>

						<p>Feature flags or toggles allow features to easily be enabled for users to test in a more realistic environment.</p>

						<p>We've got no flags so time to show a tutorial on how to add flags, maybe a link to the README etc.</p>

					<?php } else { ?>

					<div class="wrap">

						<h1>Flagpole</h1>

						<?php if ( isset( $_GET['error'] ) ) { ?>

							<?php $flagpole_error_key = sanitize_text_field( wp_unslash( $_GET['error'] ) ); ?>

							<div class="notice notice-<?php echo wp_kses_post( Flagpole::init()->get_admin_message_class( $flagpole_error_key ) ); ?> is-dismissible">
								<p><?php echo wp_kses_post( Flagpole::init()->get_admin_error_message( $flagpole_error_key ) ); ?></p>
								<button type="button" class="notice-dismiss">
									<span class="screen-reader-text">Dismiss this notice.</span>
								</button>
							</div>

					<?php } ?>

						<h2 class="nav-tab-wrapper">
							<a href="?page=flagpole&tab=flags" class="nav-tab <?php echo wp_kses_post( 'flags' === $flagpole_active_tab ? 'nav-tab-active' : '' ); ?>">Flags</a>
							<a href="?page=flagpole&tab=groups" class="nav-tab <?php echo wp_kses_post( 'groups' === $flagpole_active_tab ? 'nav-tab-active' : '' ); ?>">Groups</a>
						</h2>

						<div class="notice-container"></div>

						<?php

						include_once plugin_dir_path( __FILE__ ) . '/settings-page/settings-page-' . $flagpole_active_tab . '.php';

						?>

					<?php } ?>

				</div>
				<?php
			}
		);
	}
);
