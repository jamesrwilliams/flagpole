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
					border: 1px solid rgba(0,0,0,.25);

				}

				.status-marker-enabled { background: #3ACC69; }

				input[data-status]{

					font-weight: bold;

				}

				input[data-status="enabled"]{

					background: rgba(240,72,72, .3) !important;
					border: 0 !important;
					color: rgba(0,0,0, .5) !important;
					
					
				}				

				pre { margin: 0; }

			</style>
			
			<?php $available_flags = featureFlags::init()->get_flags(); ?>
			<?php $enforced_flags = featureFlags::init()->get_flags(true); ?>

			<div class="wrap">
				
				<h1>Feature Flags</h1>

				<hr>

				<p>Feature flags or toggles allow features to easily be enabled for users to test in a more realistic environment. </p>
					
				<div class="notice-container"></div>
				
				<?php if($available_flags){ ?>

					<h2>Available feature flags</h2>

					<table class="widefat">
						<thead>
							<tr>
								<th class="row-title">Feature</th>
								<th>Key</th>
								<th>Description</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							
								<?php foreach ($available_flags as $key => $flag) { ?>

									<?php $enabled = is_enabled($flag->get_key(false)); ?>

									<tr class="<?php echo ($key % 2 == 0 ? 'alternate' : null); ?>">
										<td class="row-title"><span class="status-marker <?php echo ($enabled ? 'status-marker-enabled' : null ); ?>" title="<?php $flag->get_name() ?> is currently <?php echo ($enabled ? 'enabled' : 'disabled' ); ?>."></span><?php $flag->get_name(); ?></td>
										<td><pre><?php $flag->get_key(); ?></pre></td>
										<td><?php $flag->get_description(); ?></td>
										<td><?php 

											if($enabled){

												submit_button( 'Disable', 'small', 'featureFlags-disable', false, ['id' => 'flagActivateButton', 'data-status' => 'enabled']);
											
											} else {

												submit_button( 'Enable', 'small', 'featureFlags-enable', false, ['id' => 'flagActivateButton', 'data-status' => 'disabled']);

											} ?>
							
										</td>
									</tr>
						
								<?php } ?>

						</tbody>
					</table>

				<?php } ?>

				<?php if($enforced_flags){ ?> 
					
					<h2>Enforced feature flags</h2>

					<p>Features listed below are currently configured to be <code>enforced</code> by default by the developers. These are flags that will be removed from the website code soon.</p>

					<table class="widefat">
						<thead>
							<tr>
								<th class="row-title">Feature</th>
								<th>Key</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody>
							
								<?php foreach ($enforced_flags as $key => $flag) { ?>

									<?php $enabled = is_enabled($flag->get_key(false)); ?>

									<tr class="<?php echo ($key % 2 == 0 ? 'alternate' : null); ?>">
										<td class="row-title"><?php $flag->get_name(); ?></td>
										<td><pre><?php $flag->get_key(); ?></pre></td>
									</tr>
						
								<?php } ?>

						</tbody>
					</table>

				<?php } ?>

				<script>

					jQuery(document).ready(function($){

						$("input#flagActivateButton").on('click', function(e){

							var $button = e.target;
							var featureKey = $button.parentElement.parentElement.querySelector('pre').innerHTML;

							console.log(featureKey);

							if(featureKey){

							//*

								$.ajax({
								
									type: "POST",
									url: ajaxurl,
									data: { action: 'featureFlag_enable' , featureKey: featureKey }
								
								}).done(function( msg ) {

									window.location.reload();
								
								}).fail(function(error) {

									$(".notice-container").html('<div class="notice notice-error is-dismissible"><p>Error cannot process <code>' + error.responseJSON.response + '</code></p></div>')
								
								});

							} else {

								$(".notice-container").html('<div class="notice notice-error is-dismissible"><p>Error: missing featureKey</p></div>');

							}

							// */

						});

					});

				</script>

			</div>
			<?php
		});
	});
