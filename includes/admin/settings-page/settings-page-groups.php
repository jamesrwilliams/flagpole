<?php
/**
 * settings-page-groups.php
 *
 * @package feature-flags
 */

use FeatureFlags\FeatureFlags;

?>

<?php

	$available_groups = FeatureFlags::init()->get_groups();
	$available_flags = FeatureFlags::init()->get_flags();

?>



<h2>Groups</h2>

<table class="widefat">
	<thead>
	<tr>
		<th class="row-title">Group Name</th>
		<th>Key</th>
		<th>Features</th>
	</tr>
	</thead>
	<tbody>
		<?php foreach ( $available_groups as $group ) { ?>
		<tr>
			<td class="row-title"><?php echo $group->get_name(); ?></td>
			<td><code><?php echo $group->get_key(); ?></code></td>
			<td>
				<?php

				if ( count( $group->get_flags() ) > 0 ) {
					foreach ( $group->get_flags() as $flag ) {
						echo $flag;
					}
				} else {
					echo 'No flags in group.';
				}

					?>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<h2>Add flag to group</h2>

<form>
	<label for="flag-to-add">Add flag</label>
	<select name="flag-to-add" id="flag-to-add">
		<?php foreach ( $available_flags as $flag ) { ?>
		<option value="<?php echo $flag->get_key(); ?>"><?php echo $flag->get_name(); ?></option>
		<?php } ?>
	</select>
	<label for="flag-to-add">to group</label>
	<select name="flag-to-add" id="flag-to-add">
		<?php foreach ( $available_groups as $group ) { ?>
			<option value="<?php echo $group->get_key(); ?>"><?php echo $group->get_name(); ?></option>
		<?php } ?>
	</select>
	<input class="button-primary" type="submit" value="Add">
</form>

<h2>Add New Group</h2>

<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="new-group">

	<input type="hidden" name="action" value="ff_register_group">

	<label for="group-name">Group Name</label>
	<br>
	<input type="text" name="group-name" class="regular-text" id="group-name">
	<br>
	<label for="group-key">Group Key</label>
	<br>
	<input type="text" name="group-key" class="regular-text" id="group-key">
	<br>
	<input class="button-primary" type="submit" value="Create" />
</form>
