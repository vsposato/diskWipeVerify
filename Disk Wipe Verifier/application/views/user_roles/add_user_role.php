<div class="span-18">
	<?php if (! $this->session->userdata('logged_in')) { redirect('/users/login'); }  ?>
	<?php echo form_open('user_roles/add_user_role');?>
	
	<fieldset>
		<legend>Add User Role</legend>
	
		<?php echo form_error('role_name');?>
		<label for="role_name" class="caps">Role Name</label>
		<input type="text" name="role_name" value="<?php echo set_value('role_name','');?>" />
		<br />
		<?php echo form_error('active');?>
		<label for="active" class="caps">Is Active?</label>
		<input type="radio" name="active" value="1" checked>Active 
		&nbsp;
		<input type="radio" name="active" value="0">Inactive
		<br /><br />
		<?php echo form_error('role_description'); ?>
		<label for="role_description" class="caps">Role Description</label>
		<br />
		<textarea rows="5" cols="20" name="role_description"><?php echo set_value('role_description','');?></textarea>
		<br />
		<input type="submit" name="submit" value="Add New Role" />	
	</fieldset>
	
	
	<?php echo form_close();?>
</div>

<div class="span-2 last"></div>

