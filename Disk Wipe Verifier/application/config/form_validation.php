<?php

$config = array(
	'users/edit_user' => array(
		array(
			'field' => 'id',
			'label' => 'User ID',
			'rules' => 'required'),
		array(
			'field' => 'user_role_id',
			'label' => 'User Role',
			'rules' => 'required'),
		array(
			'field' => 'last_name',
			'label' => 'Last Name',
			'rules' => 'trim|required|max_length[30]|xss_clean'),
		array(
			'field' => 'first_name',
			'label' => 'First Name',
			'rules' => 'trim|required|max_length[30]|xss_clean'),
		array(
			'field' => 'email_address',
			'label' => 'Email Address',
			'rules' => 'trim|required|min_length[9]|max_length[90]|valid_email|xss_clean'),
		),
	'users/edit_user_with_password' => array(
		array(
			'field' => 'id',
			'label' => 'User ID',
			'rules' => 'required'),
		array(
			'field' => 'user_role_id',
			'label' => 'User Role',
			'rules' => 'required'),
		array(
			'field' => 'last_name',
			'label' => 'Last Name',
			'rules' => 'trim|required|max_length[30]|xss_clean'),
		array(
			'field' => 'first_name',
			'label' => 'First Name',
			'rules' => 'trim|required|max_length[30]|xss_clean'),
		array(
			'field' => 'email_address',
			'label' => 'Email Address',
			'rules' => 'trim|required|min_length[9]|max_length[90]|valid_email|xss_clean'),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'required|matches[confirm_password]|sha1'),
		array(
			'field' => 'confirm_password',
			'label' => 'Password Confirmation',
			'rules' => 'required|matches[password]|sha1'),
		),
	'users/add_user' => array(
		array(
			'field' => 'user_role_id',
			'label' => 'User Role',
			'rules' => 'required'),
		array(
			'field' => 'last_name',
			'label' => 'Last Name',
			'rules' => 'trim|required|max_length[30]|xss_clean'),
		array(
			'field' => 'first_name',
			'label' => 'First Name',
			'rules' => 'trim|required|max_length[30]|xss_clean'),
		array(
			'field' => 'email_address',
			'label' => 'Email Address',
			'rules' => 'trim|required|min_length[9]|max_length[90]|valid_email|xss_clean'),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'required|matches[confirm_password]|sha1'),
		array(
			'field' => 'confirm_password',
			'label' => 'Password Confirmation',
			'rules' => 'required|matches[password]|sha1'),
		),
	'users/login' => array(
		array(
			'field' => 'email_address',
			'label' => 'Email Address',
			'rules' => 'trim|required|min_length[9]|max_length[90]|valid_email|xss_clean'),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'required|sha1')
		),
	'user_roles/edit_user_role' => array(
			'field' => 'id',
			'label' => 'User Role ID',
			'rules' => 'required'),
		array(
			'field' => 'role_name',
			'label' => 'Role Name',
			'rules' => 'trim|required|min_length[4]|max_length[120]|xss_clean'),
		array(
			'field' => 'role_description',
			'label' => 'Role Description',
			'rules' => 'trim|required|xss_clean'),
		array(
			'field' => 'active',
			'label' => 'Is Active',
			'rules' => 'required'),
	'user_roles/add_user_role' => array(
			'field' => 'role_name',
			'label' => 'Role Name',
			'rules' => 'trim|required|min_length[4]|max_length[120]|xss_clean'),
		array(
			'field' => 'role_description',
			'label' => 'Role Description',
			'rules' => 'trim|required|xss_clean'),
		array(
			'field' => 'active',
			'label' => 'Is Active',
			'rules' => 'required')
	);
	
/* End of file form_validation.php */
/* Location: ./application/config/form_validation.php */