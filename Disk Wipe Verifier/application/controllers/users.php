<?php

class Users extends CI_Controller {
	
	function __construct() {
		/* Call the parent constructor to make sure that the controller is fully ready */
		parent::__construct();
		$this->load->model('User');
		$this->load->model('User_role', 'Role');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}
	
	public function index() {		
		//Find all users
		$this->User->find_all_users();
		
		//Call the find_all_users model function
		$data['all_users'] = $this->User->all_users;
		
		//Get all field names from users table
		$data['headers'] = $this->db->list_fields('users');
		
		//Set the view variables for title & heading
		$header['Title'] = "User Management - User Listing";
		$header['Heading'] = "Widget Corp User Management";
		
		//Take result the find_all_users function and pass it to the index view
		$this->load->view('defaults/header', $header);
		$this->load->view('defaults/navigation');
		$this->load->view('defaults/sidebar');
		$this->load->view('users/index', $data);
		$this->load->view('defaults/footer');
		
		
	}

	public function logout() {
		//Check to see if user is logged in
		if ($this->session->userdata('logged_in')) {
			//User is logged in - so log out
			$this->session->sess_destroy();
			$this->session->set_flashdata('status_class', 'notice');
			$this->session->set_flashdata('status_message', 'You have been logged out!');
			redirect('/users/login');	
		} else {
			//User is not logged in - so do nothing
			$this->session->set_flashdata('status_class', 'error');
			$this->session->set_flashdata('status_message', 'You are not currently logged in!');
			redirect('/');			
		}
		
	}
	
	public function login() {
		//Check to see if user is already logged in
		if (! $this->session->userdata('logged_in')) {
			//User not already logged in
			//Check to see if the submit button has been pushed
			if (array_key_exists('submit', $_POST)) {
				//Submit button selected
				//Check to make sure that a valid username and password were submitted
				if ($this->form_validation->run() === FALSE) {
					//Form validation failed - represent the user with a login page
					//Set the view variables for title & heading
					$header['Title'] = "Widget Corp - Login";
					$header['Heading'] = "Widget Corp User Management";
					
					//Take result the find_all_users function and pass it to the index view
					$this->load->view('defaults/header', $header);
					$this->load->view('users/login');
					$this->load->view('defaults/footer');				
				} else {
					//Form validation succeeded so call the user functions to login
					$email_address = $this->input->post('email_address');
					$password = $this->input->post('password');
					$logged_in = $this->User->login_user($email_address, $password);

					if ($logged_in) {
						//User successfully logged in
						redirect('/users/index');
					} else {
						//User not successfully logged in
						//Set the view variables for title & heading
						$header['Title'] = "Widget Corp - Login";
						$header['Heading'] = "Widget Corp User Management";
						
						//Take result the find_all_users function and pass it to the index view
						$this->load->view('defaults/header', $header);
						$this->load->view('users/login');
						$this->load->view('defaults/footer');									
					}
				}
			} else {
				//Submit button not selected
				//Set the view variables for title & heading
				$header['Title'] = "Widget Corp - Login";
				$header['Heading'] = "Widget Corp User Management";
				
				//Take result the find_all_users function and pass it to the index view
				$this->load->view('defaults/header', $header);
				$this->load->view('users/login');
				$this->load->view('defaults/footer');			
			}
		} else {
			//User is already logged in so send to the index
			$this->session->set_flashdata('status_class', 'notice');
			$this->session->set_flashdata('status_message', 'You are already logged in!');
			redirect('/users/index/');
		}
	}
	
	public function edit_user($id = null) {
		if (! array_key_exists('submit', $_POST) ) {
			//Check to determine something numeric was passed to the function
			if ($id && is_numeric($id)) {
				//Load the user information and pass it to the edit user view
				$this->User->find_user_by_id($id);
				$this->Role->find_all_active_roles();
				$data['valid_results'] = TRUE;
				$data['id'] = $this->User->selected_user['id'];
				$data['user_role_id'] = $this->User->selected_user['user_role_id'];
				$data['last_name'] = $this->User->selected_user['last_name'];
				$data['first_name'] = $this->User->selected_user['first_name'];
				$data['email_address'] = $this->User->selected_user['email_address'];
				$data['password'] = $this->User->selected_user['password'];
				$data['roles_available'] = $this->Role->all_active_roles;
			} else {
				
				$data['valid_results'] = FALSE;
				$data['selected_user'] = "Invalid user selected!";
				
			}

			//Take the results and load the view
			//Set the view variables for title & heading
			$header['Title'] = "User Management - Edit User";
			$header['Heading'] = "Widget Corp User Management";
			
			//Take result the find_all_users function and pass it to the index view
			$this->load->view('defaults/header', $header);
			$this->load->view('defaults/navigation');
			$this->load->view('defaults/sidebar');
			$this->load->view('users/edit_user', $data);
			$this->load->view('defaults/footer');				
			
		} else {

			if ($this->input->post('password') || $this->input->post('confirm_password')) {
				$form_validation = $this->form_validation->run('users/edit_user_with_password');
			} else {
				$form_validation = $this->form_validation->run();
			}
			
			if ($form_validation === FALSE) {
				//What to do if validation fails
				//Set the view variables for title & heading
				$this->Role->find_all_active_roles();
				$data['roles_available'] = $this->Role->all_active_roles;
				
				$header['Title'] = "User Management - Edit User";
				$header['Heading'] = "Widget Corp User Management";
				
				$data['id'] = '';
				$data['user_role_id'] = '';
				$data['last_name'] = '';
				$data['first_name'] = '';
				$data['email_address'] = '';
				$data['password'] = '';
				$data['confirm_password'] = '';
				
				$this->load->view('defaults/header', $header);
				$this->load->view('defaults/navigation');
				$this->load->view('defaults/sidebar');
				$this->load->view('users/edit_user', $data);
				$this->load->view('defaults/footer');			

			} else {

				//What to do if validation passes
				$update_user = $this->input->post();
				if ($this->User->update_user($update_user)) {
					//We successfully updated the user so redirect
					redirect('/users/index/');
				} else {
					//We failed to update the user bring us back to the page
					$this->Role->find_all_active_roles();
					$data['roles_available'] = $this->Role->all_active_roles;
					
					$header['Title'] = "User Management - Edit User";
					$header['Heading'] = "Widget Corp User Management";
					
					$data['id'] = '';
					$data['user_role_id'] = '';
					$data['last_name'] = '';
					$data['first_name'] = '';
					$data['email_address'] = '';
					$data['password'] = '';
					$data['confirm_password'] = '';
					
					$this->load->view('defaults/header', $header);
					$this->load->view('defaults/navigation');
					$this->load->view('defaults/sidebar');
					$this->load->view('users/edit_user', $data);
					$this->load->view('defaults/footer');										
				}
			}
		}
	}
	
	public function delete_user($id = null) {
		//Check to make sure that the function was passed a valid ID
		
		if (! empty($id) && is_numeric($id)) {
			// Valid ID passed call the delete user model function
			$delete_result = $this->User->delete_user($id);
			
			if ($delete_result) {
				//Delete succeeded pass the result back
				$this->session->set_flashdata('status_message','The user was deleted successfully!');
				$this->session->set_flashdata('status_class','notice');
				redirect('/users/index/');
			} else {
				//Delete failed pass the result back
				$this->session->set_flashdata('status_message','The user was not deleted successfully!');
				$this->session->set_flashdata('status_class','error');
				redirect('/users/index/');
			}
		} else {
			//Invalid ID passed in so pass a message back
			$this->session->set_flashdata('status_message','The user was not deleted successfully!');
			$this->session->set_flashdata('status_class','error');
			redirect('/users/index/');
		}
	}
	
	public function add_user() {
		if (! array_key_exists('submit', $_POST) ) {
			//Check to determine something numeric was passed to the function
			$this->Role->find_all_active_roles();
			$data['roles_available'] = $this->Role->all_active_roles;

			//Take the results and load the view
			//Set the view variables for title & heading
			$header['Title'] = "User Management - Add User";
			$header['Heading'] = "Widget Corp User Management";
			
			//Take result the find_all_users function and pass it to the index view
			$this->load->view('defaults/header', $header);
			$this->load->view('defaults/navigation');
			$this->load->view('defaults/sidebar');
			$this->load->view('users/add_user', $data);
			$this->load->view('defaults/footer');				
			
		} else {

			if ($this->form_validation->run() === FALSE) {
				//What to do if validation fails
				//Set the view variables for title & heading
				$this->Role->find_all_active_roles();
				$data['roles_available'] = $this->Role->all_active_roles;
				
				$header['Title'] = "User Management - Add User";
				$header['Heading'] = "Widget Corp User Management";
				
				$data['id'] = '';
				$data['user_role_id'] = '';
				$data['last_name'] = '';
				$data['first_name'] = '';
				$data['email_address'] = '';
				$data['password'] = '';
							
				$this->load->view('defaults/header', $header);
				$this->load->view('defaults/navigation');
				$this->load->view('defaults/sidebar');
				$this->load->view('users/add_user', $data);
				$this->load->view('defaults/footer');			
			} else {
				//What to do if validation passes
				$inserted_user = $this->input->post();

				if ($this->User->insert_user($inserted_user)) {
					//We successfully inserted the user so redirect
					redirect('/users/index/');
				} else {
					
					//We failed to update the user bring us back to the page
					$this->Role->find_all_active_roles();
					$data['roles_available'] = $this->Role->all_active_roles;
					
					$header['Title'] = "User Management - Add User";
					$header['Heading'] = "Widget Corp User Management";
					
					$data['id'] = '';
					$data['user_role_id'] = '';
					$data['last_name'] = '';
					$data['first_name'] = '';
					$data['email_address'] = '';
					$data['password'] = '';
			
					$this->load->view('defaults/header', $header);
					$this->load->view('defaults/navigation');
					$this->load->view('defaults/sidebar');
					$this->load->view('users/add_user', $data);
					$this->load->view('defaults/footer');										
				}
			}
		}
	}
}

/* End of file: users.php
 * Location: application/controllers/users.php
 */