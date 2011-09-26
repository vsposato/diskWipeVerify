<div class="span-18">
<?php if (! $this->session->userdata('logged_in')) { redirect('/users/login'); }  ?>

<?php
	//Set the values from the database or a previously failed submission
	$id = set_value('id') ? set_value('id') : $id;
	$role_name = set_value('role_name') ? set_value('role_name') : $role_name;
	$role_description = set_value('role_description') ? set_value('role_description') : $role_description;
	$active = set_value('active') ? set_value('active') : $active;
?>

	<?php echo form_open('user_roles/edit_user_role');?>
	
	<fieldset>
		<legend>Edit User Role</legend>
		<input type="hidden" name="id" value="<?php echo $id;?>" />
		<?php echo form_error('role_name');	?>
		<label for="role_name" class="caps">Role Name</label>
		<input type="text" name="role_name" value="<?php echo $role_name;?>" />
		<br />
		<?php echo form_error('active');?>
		<label for="active" class="caps">Is Active?</label>
		<input type="radio" name="active" value="1" <?php echo ($active == 1) ? "checked" : "";?>>Active 
		&nbsp;
		<input type="radio" name="active" value="0" <?php echo ($active == 0) ? "checked" : "";?>>Inactive
		<br /><br />
		<?php echo form_error('role_description');?>
		<label for="role_description" class="caps">Role Description</label>
		<br />
		<textarea rows="5" cols="20" name="role_description"><?php echo $role_description;?></textarea>
		<br />
		<input type="submit" name="submit" value="Edit User Role" />	
	</fieldset>

	
	<?php echo form_close();?>
</div>

<div class="span-2 last"></div>

