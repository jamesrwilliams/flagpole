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
		add_submenu_page('tools.php', 'Feature Flags', 'Feature Flags', 'administrator', 'feature-flags', function() {
			?>
			
			<?php $flags = featureFlags::init()->get_flags(); ?>

			<div class="wrap">
				
				<h1>Feature Flags</h1>
				
				<table class="widefat">
					<thead>
						<tr>
							<th class="row-title">Feature</th>
							<th>Key</th>
							<th>Description</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						
						<?php if($flags){ ?> 
				
							<?php foreach ($flags as $flag) { ?>

								<tr>
									<td class="row-title"><?php $flag->name(); ?></td>
									<td><code><?php $flag->key(); ?></code></td>
									<td><?php $flag->description(); ?></td>
									<td><?php $flag->status(); ?></td>
								</tr>
					
							<?php } ?>
						
						<?php } ?>

					</tbody>
				</table>

			</div>
			<?php
		});
	});

	/* Register fields
	-------------------------------------------------------- */
	add_action('admin_init', function() {
		
		// Admin Init
		
	});










