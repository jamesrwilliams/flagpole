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

		add_submenu_page('tools.php', 'Feature Flags', 'Feature Flags', 'administrator', 'feature-flags', function() { ?>

			<style>

				.status-marker { 
					
					display: inline-block;
					height: 12px; 
					width: 12px;
					border-radius: 12px;
					background: #E3E3E0;
					margin-right: 10px;
					transform: translateY(2px);
					border: 1px solid rgba(0,0,0,0.25);

				}

				pre { margin: 0;  }

				.status-marker-enabled { 
					
					background: #3ACC69;
				
				}

			</style>
			
			<?php $flags = featureFlags::init()->get_flags(); ?>

			<div class="wrap">
				
				<h1>Feature Flags</h1>
						
				<?php $flagStatus = isEnabled('mega-menu'); ?>

				<div class="notice notice-info"><p>Testing flag: <strong><?php echo ($flagStatus ? 'ENABLED' : 'DISABLED' ); ?></strong></p></div>
				
				<table class="widefat">
					<thead>
						<tr>
							<th class="row-title">Feature</th>
							<th>Key</th>
							<th>Description</th>
							<th>Enable for user</th>
						</tr>
					</thead>
					<tbody>
						
						<?php if($flags){ ?> 
				
							<?php foreach ($flags as $key => $flag) { ?>

								<tr class="<?php echo ($key % 2 == 0 ? 'alternate' : null); ?>">
									<td class="row-title"><span class="status-marker <?php echo ($flag->get_enforced() ? 'status-marker-enabled' : null ); ?>" title="<?php $flag->get_enforced(); ?>"></span><?php $flag->get_name(); ?></td>
									<td><pre><?php $flag->get_key(); ?></pre></td>
									<td><?php $flag->get_description(); ?></td>
									<td><?php submit_button( 'Enable', 'small', 'featureFlags-enable', false, ['id' => 'flagActivateButton']); ?></td>
								</tr>
					
							<?php } ?>
						
						<?php } ?>

					</tbody>
				</table>
								
				<p class=""><?php echo date('l jS \of F Y @ h:i:s A'); ?></p>

				<script>

					jQuery(document).ready(function($){

						$("input#flagActivateButton").on('click', function(e){

							console.log(e);

						});

					});

				</script>

			</div>
			<?php
		});
	});
