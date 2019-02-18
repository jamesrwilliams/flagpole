<?php
/**
 * Template for the WP-Feature-Flags admin pages.
 *
 * @package feature-flags
 */

use FeatureFlags\FeatureFlags;

	$available_groups = FeatureFlags::init()->get_groups();
	$available_flags  = FeatureFlags::init()->get_flags();

?>

<h2>Groups</h2>

<?php if ( count( $available_groups ) === 0 ) { ?>

	<h1>No groups - You should add one, they're great.</h1>

<?php } else { ?>

	<table class="widefat groups_table">
		<thead>
		<tr>
			<th>&nbsp;</th>
			<th class="row-title">Group Name</th>
			<th>Key</th>
			<th>Description</th>
			<th>Features</th>
			<th colspan="3">Actions</th>
		</tr>
		</thead>
		<tbody>

		<?php foreach ( $available_groups as $key => $group ) { ?>
			<tr class="<?php echo( 0 === $key % 2 ? 'alternate' : null ); ?>">
				<td class="has-icon">
					<?php $img_path = FM_PLUGIN_URL . 'assets/images/' . ( $group->is_private() ? 'private' : 'public' ) . '.icon.svg'; ?>

					<img
						src="<?php echo esc_url( $img_path ); ?>"
						alt="<?php echo wp_kses_post( $group->is_private() ? 'This group is private.' : 'This group is public.' ); ?>">
				</td>
				<td class="row-title">
					<strong><?php echo wp_kses_post( $group->get_name() ); ?></strong>
				</td>
				<td><code><?php echo wp_kses_post( $group->get_key() ); ?></code></td>
				<td><?php echo wp_kses_post( $group->get_description() ); ?></td>
				<td>
					<?php if ( count( $group->get_flags() ) > 0 ) { ?>

						<?php foreach ( $group->get_flags() as $flag ) { ?>

							<div class="ff-badge">
								<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<?php $flag_obj = FeatureFlags::init()->find_flag( $flag ); ?>
									<?php wp_nonce_field( 'ff_remove_flag_from_group' ); ?>
									<input type="hidden" name="action" value="ff_remove_flag_from_group">
									<input type="hidden" name="flag" value="<?php echo wp_kses_post( $flag_obj->key ); ?>">
									<input type="hidden" name="group" value="<?php echo wp_kses_post( $group->get_key() ); ?>">
									<span class="ff_group-flag-label" title="Key: <?php echo wp_kses_post( $flag_obj->key ); ?>">
										<?php echo wp_kses_post( $flag_obj->get_name() ); ?>
									</span>
									<button class="ff_remove_btn" type="submit" title="Remove '<?php echo wp_kses_post( $flag_obj->name ); ?>' from '<?php echo wp_kses_post( $group->name ); ?>'">&#10005;</button>
								</form>
							</div>

						<?php } // Close Foreach ?>

					<?php } else { ?>
						<span class="no-flags">No flags in group. <a href="#add-to-group">Add one.</a></span>
					<?php } // Close Else ?>

				</td>
				<td>
					<!-- [Preview] -->
				</td>
				<td>
					<!-- [Publish] -->
				</td>
				<td>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="ff_delete_group">
						<input type="hidden" name="key" value="<?php echo wp_kses_post( $group->get_key() ); ?>">
						<?php wp_nonce_field( 'ff_delete_group' ); ?>
						<?php
							submit_button(
								'Delete',
								'small',
								'featureFlagsBtn_delete_group',
								false,
								[
									'class'       => 'action-btn',
									'data-action' => 'toggleFeatureFlag',
									'data-status' => 'enabled',
								]
							);
						?>
					</form>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

<?php } ?>

<h2>Add flag to group</h2>

<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="add-to-group">
	<input type="hidden" name="action" value="ff_add_to_group">
	<?php wp_nonce_field( 'ff-add-to-group' ); ?>
	<label for="selected-flag">Add flag</label>
	<select name="selected-flag" id="selected-flag">
		<option disabled selected>Select flag...</option>
		<?php foreach ( $available_flags as $flag ) { ?>
			<option value="<?php echo wp_kses_post( $flag->get_key() ); ?>"><?php echo wp_kses_post( $flag->get_name() ); ?></option>
		<?php } ?>
	</select>
	<label for="selected-group">to group</label>
	<select name="selected-group" id="selected-group">
		<option disabled selected>Select group...</option>
		<?php foreach ( $available_groups as $group ) { ?>
			<option value="<?php echo wp_kses_post( $group->get_key() ); ?>"><?php echo wp_kses_post( $group->get_name() ); ?></option>
		<?php } ?>
	</select>
	<input class="button-primary" type="submit" value="Add">
</form>

<h2>Add New Group</h2>

<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="new-group">

	<input type="hidden" name="action" value="ff_register_group">
	<?php wp_nonce_field( 'register-group' ); ?>

	<label for="group-name">Group Name</label>
	<br>
	<input type="text" name="group-name" class="regular-text" id="group-name">
	<br>
	<label for="group-key">Group Key</label>
	<br>
	<input type="text" name="group-key" class="regular-text" id="group-key">
	<br>
	<div class="radio-container">

		<p>Flag group visibility:</p>

		<label>
			<input type="radio" name="group-private" value="true" checked>
			<span><strong>Private</strong> - Requires users to be logged in to view.</span>
		</label>

		<br>

		<label>
			<input type="radio" name="group-private" value="false">
			<span><strong>Public</strong> - Makes the group publicly queryable.</span>
		</label>

		<br>
		<br>

	</div>
	<label for="group-description">Group Description</label>
	<textarea name="group-description" id="group-description" cols="30" rows="10"></textarea>
	<br>
	<input class="button-primary" type="submit" value="Create" />
</form>
