<div class="span-10 last prepend-7 append-7 prepend-top append-bottom">
	<p class="<?php echo $this->session->flashdata('status_class');?>"><?php echo $this->session->flashdata('status_message'); ?></p>
	<br /> 

	<?php echo form_open('users/login');?>
		<fieldset>
			<legend>User Login</legend>
			<table class="login-box">
				<tr>
					<td><?php echo form_error('email_address');?></td>
				</tr>
				<tr>
					<td align="right"><label for="email_address" class="caps">E-mail Address</label></td>
					<td><input type="text" name="email_address" size="30" value="<?php echo set_value('email_address','');?>" /></td>
				</tr>
				<tr>	
					<?php echo form_error('password');?>
				</tr>
				<tr>
					<td align="right"><label for="password" class="caps">Password</label></td>
					<td><input type="password" name="password" size="30" value="" /></td>
				</tr>
				<tr>
					<td><input type="submit" name="submit" value="Login" /></td>
				</tr>
			</table>
		</fieldset>
	
	<?php echo form_close();?>
</div>
<div class="clear"></div>