<div class="span-20 last">
<?php
	echo form_open('users/edit');
	
		echo form_fieldset('Edit User');
		
			$id = set_value('id') ? set_value('id') : $id;
			echo form_hidden('id', $id);
			
			echo form_error('last_name');
			echo form_error('first_name');
			$last_name = set_value('last_name') ? set_value('last_name') : $last_name;
			echo form_label('Last Name: ', 'last_name');
			echo form_input('last_name', $last_name);
			echo "&nbsp; &nbsp;";
			$first_name = set_value('first_name') ? set_value('first_name') : $first_name;
			echo form_label('First Name: ', 'first_name');
			echo form_input('first_name', $first_name);
			echo "<br/>";	
			echo form_error('email_address');
			$email_address = set_value('email_address') ? set_value('email_address') : $email_address;
			echo form_label('Email Address: ', 'email_address');
			echo form_input('email_address', $email_address);
			echo "<br/>";	
			// create the dropdown for the user role
			foreach ($roles_available as $role) {
				//Take the roles passwed in and populate them to an array
				$user_roles[$role['id']] = $role['role_name'];
			}
			echo form_error('user_role_id');
			echo form_label('User Role: ', 'user_role_id');
			$user_role_id = set_value('user_role_id') ? set_value('user_role_id') : $user_role_id;
			echo form_multiselect('user_role_id', $user_roles, $user_role_id);
			echo "<br/>";
			
			echo form_error('password');
			echo form_error('confirm_password');
			echo form_label('Password: ', 'password');
			echo form_password('password','');
			echo "&nbsp; &nbsp;";
			echo form_label('Confirm Password: ', 'confirm_password');
			echo form_password('confirm_password','');
			echo "<br/>";
			echo form_submit('submit', 'Edit User');
		
		echo form_fieldset_close();
	
	echo form_close();
?>
</div>

