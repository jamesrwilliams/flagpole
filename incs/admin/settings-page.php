<?php

	/* Register settings
	-------------------------------------------------------- */
	add_action('admin_init', function() {
		
		register_setting('ff-settings-page', 'ff_client_secret', function($posted_data) {
			if (!$posted_data) {
				add_settings_error('ff_client_secret', 'ff_updated', 'Error Message', 'error');
				return false;
			}

			return $posted_data;

		});

	});

	/* Settings page
	-------------------------------------------------------- */
	add_action('admin_menu', function() {
		add_submenu_page('options-general.php', 'Feature Flags', 'Feature Flags', 'administrator', 'feature-flags', function() {
			?>
			<div class="wrap">
				
				<h1>Feature Flags</h1>
				<hr>
					
			</div>
			<?php
		});
	});

	/* Register fields
	-------------------------------------------------------- */
	add_action('admin_init', function() {
		
		// Admin Init
		
	});










