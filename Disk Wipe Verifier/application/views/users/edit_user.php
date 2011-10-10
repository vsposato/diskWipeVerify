<div class="span-18">
<?php /* if (! $this->session->userdata('logged_in')) { redirect('/users/login'); } */ ?>

<?php
	//Set the values from the database or a previously failed submission
	$id = set_value('id') ? set_value('id') : $id;
	$last_name = set_value('last_name') ? set_value('last_name') : $last_name;
	$first_name = set_value('first_name') ? set_value('first_name') : $first_name;
	$email_address = set_value('email_address') ? set_value('email_address') : $email_address;
	$user_role_id = set_value('user_role_id') ? set_value('user_role_id') : $user_role_id;
?>

	<?php echo form_open('users/edit_user');?>
	
	<fieldset>
		<legend>Edit User</legend>
		<input type="hidden" name="id" value="<?php echo $id;?>" />
		<?php 
			echo form_error('last_name');
			echo form_error('first_name');
		?>
		<label for="last_name" class="caps">Last Name</label>
		<input type="text" name="last_name" value="<?php echo $last_name;?>" />
		&nbsp; &nbsp;
		<label for="first_name" class="caps">First Name</label>
		<input type="text" name="first_name" value="<?php echo $first_name;?>" />
		<br />
		<?php echo form_error('email_address');?>
		<label for="email_address" class="caps">E-mail Address</label>
		<input type="text" name="email_address" value="<?php echo $email_address;?>" />
		<br />
		<?php echo form_error('user_role_id');?>
		<label for="user_role_id" class="caps">User Role</label>
		<?php 
			foreach ($roles_available as $role) {
				//Take the roles passwed in and populate them to an array
				$user_roles[$role['id']] = $role['role_name'];
			}
			echo form_multiselect('user_role_id', $user_roles, $user_role_id);

			echo form_error('password');
			echo form_error('confirm_password');
		?>
		<br />
		<label for="password" class="caps">Password</label>
		<input type="password" name="password" value="" />
		&nbsp; &nbsp;
		<label for="confirm_password" class="caps">Confirm Password</label>
		<input type="password" name="confirm_password" value="" />
		<br />
		<input type="submit" name="submit" value="Edit User" />	
	</fieldset>
	
	
	<?php echo form_close();?>
</div>

<div class="span-2 last"></div>

