<div class="span-18 last">
<?php /* if (! $this->session->userdata('logged_in')) { redirect('/users/login'); }  */ ?>

<?php 
	//Humanize the headers for easy display
	$table_headers = array();
	$skip_fields = array('id', 'active');
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
	<?php foreach ($all_user_roles as $user_role):?>
	<tr>
		<?php foreach ($user_role as $field => $value):?>
			<?php if (in_array($field, $skip_fields)) {?>
			<?php 	continue;?>
			<?php } else {?>
				<td><?php echo $value;?></td>
			<?php }?>
		<?php endforeach;?>
		<!-- Use the URL helper to build an edit and delete action -->
		<td>
			<?php 
				echo anchor(array('user_roles','edit_user_role',$user_role['id']),'Edit','class="button"') . ' ' . anchor(array('user_roles','delete_user_role',$user_role['id']),'Delete',array('class' => 'button', 'onclick' => 'return confirm(\'Are you sure you want to delete this role?\')'));
			?>
		</td>
	</tr>
	<?php endforeach;?>
	<tfoot>
		<tr>
			<td><?php echo anchor(array('user_roles','add_user_role'),'Add User Role','class="button"'); ?></td>
		</tr>
	</tfoot>
</table>


</div>