<div class="span-18 last">
<?php /* if (! $this->session->userdata('logged_in')) { redirect('/users/login'); } */ ?>

<?php 
	//Humanize the headers for easy display
	$table_headers = array();
	$skip_fields = array('id','password');
	foreach ($headers as $key => $value) {
		if (in_array($value, $skip_fields)) {
			continue;
		} else {
			$table_headers[] = humanize($value);
		}
	}
?>
<!--
	Here we will build the table to display the users 
 -->
<p class="<?php echo $this->session->flashdata('status_class');?>"><?php echo $this->session->flashdata('status_message'); ?></p>
<br /> 
<table>
	<thead>
		<tr>
			<?php foreach ($table_headers as $table_header):?>
				<th><?php echo $table_header;?></th>
			<?php endforeach;?>
			<th>Actions</th>
		</tr>
	</thead>
	<?php foreach ($all_users as $user):?>
	<tr>
		<?php foreach ($user as $field => $value):?>
			<?php if (in_array($field, $skip_fields)) {?>
			<?php 	continue;?>
			<?php } else {?>
				<?php if ($field == 'user_role_id') {?>			
					<td><?php echo $this->Role->find_role_name_by_id($value);?></td>
				<?php } else {?>
					<td><?php echo $value;?></td>				
				<?php }?>
			<?php }?>
		<?php endforeach;?>
		<!-- Use the URL helper to build an edit and delete action -->
		<td>
			<?php 
				echo anchor(array('users','edit_user',$user['id']),'Edit','class="button"') . ' ' . anchor(array('users','delete_user',$user['id']),'Delete',array('class' => 'button', 'onclick' => 'return confirm(\'Are you sure you want to delete this user?\')'));
				//echo anchor(array('users','delete_user',$user['id']),'Delete','class="button"');
			?>
		</td>
	</tr>
	<?php endforeach;?>
	<tfoot>
		<tr>
			<td><?php echo anchor(array('users','add_user'),'Add New User','class="button"'); ?></td>
		</tr>
	</tfoot>
</table>


</div>