<?php

class User_roles extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->model('User_role', 'Role');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}
	
	public function index() {
		//Find all user roles
		$this->Role->find_all_roles();
		//Assign return values back to data array
		$data['all_user_roles'] = $this->Role->all_roles;
		//Get field names for the table header row
		$data['headers'] = $this->db->list_fields('user_roles');
		//Build the header information
		$header['Title'] = "User Management - User Roles Listing";
		$header['Heading'] = "Widget Corp User Management";
		//Launch the view files to show the results
		$this->load->view('defaults/header', $header);
		$this->load->view('defaults/navigation');
		$this->load->view('defaults/sidebar');
		$this->load->view('user_roles/index', $data);
		$this->load->view('defaults/footer');
		
	}
	
	public function add_user_role() {
		if (! array_key_exists('submit', $_POST) ) {
			//Take the results and load the view
			//Set the view variables for title & heading
			$header['Title'] = "User Management - Add Role";
			$header['Heading'] = "Widget Corp User Management";
			
			//Take result the find_all_users function and pass it to the index view
			$this->load->view('defaults/header', $header);
			$this->load->view('defaults/navigation');
			$this->load->view('defaults/sidebar');
			$this->load->view('user_roles/add_user_role');
			$this->load->view('defaults/footer');				
			
		} else {

			if ($this->form_validation->run() === FALSE) {
				//What to do if validation fails
				//Set the view variables for title & heading
				$header['Title'] = "User Management - Add Role";
				$header['Heading'] = "Widget Corp User Management";
				
				$data['role_name'] = '';
				$data['role_description'] = '';
				$data['active'] = '';
							
				$this->load->view('defaults/header', $header);
				$this->load->view('defaults/navigation');
				$this->load->view('defaults/sidebar');
				$this->load->view('user_roles/add_user_role', $data);
				$this->load->view('defaults/footer');			
			} else {
				//What to do if validation passes
				$inserted_user_role = $this->input->post();

				if ($this->Role->insert_user_role($inserted_user_role)) {
					//We successfully inserted the user so redirect
					redirect('/user_roles/index/');
				} else {
					
					//We failed to update the user bring us back to the page
					$header['Title'] = "User Management - Add Role";
					$header['Heading'] = "Widget Corp User Management";
					
					$data['role_name'] = '';
					$data['role_description'] = '';
					$data['active'] = '';
								
					$this->load->view('defaults/header', $header);
					$this->load->view('defaults/navigation');
					$this->load->view('defaults/sidebar');
					$this->load->view('user_roles/add_user_role', $data);
					$this->load->view('defaults/footer');										
				}
			}
		}
	}		
	
	public function edit_user_role ($id = NULL) {
		if (! array_key_exists('submit', $_POST) ) {
			//Check to determine something numeric was passed to the function
			if ($id && is_numeric($id)) {
				//Load the user information and pass it to the edit user view
				$this->Role->find_role_by_id($id);
				$data['valid_results'] = TRUE;
				$data['id'] = $this->Role->selected_role['id'];
				$data['role_name'] = $this->Role->selected_role['role_name'];
				$data['role_description'] = $this->Role->selected_role['role_description'];
				$data['active'] = $this->Role->selected_role['active'];
			} else {
				$data['valid_results'] = FALSE;
				$data['selected_user'] = "Invalid user selected!";
			}
			//Take the results and load the view
			//Set the view variables for title & heading
			$header['Title'] = "User Management - Edit Role";
			$header['Heading'] = "Widget Corp User Management";
			
			//Take result the find_all_users function and pass it to the index view
			$this->load->view('defaults/header', $header);
			$this->load->view('defaults/navigation');
			$this->load->view('defaults/sidebar');
			$this->load->view('user_roles/edit_user_role', $data);
			$this->load->view('defaults/footer');				
			
		} else {
			if ($this->form_validation->run() === FALSE) {
				//What to do if validation fails
				//Set the view variables for title & heading
				$header['Title'] = "User Management - Edit Role";
				$header['Heading'] = "Widget Corp User Management";
				
				$data['id'] = '';
				$data['role_name'] = '';
				$data['role_description'] = '';
				$data['active'] = '';
				
				$this->load->view('defaults/header', $header);
				$this->load->view('defaults/navigation');
				$this->load->view('defaults/sidebar');
				$this->load->view('user_roles/edit_user_role', $data);
				$this->load->view('defaults/footer');			

			} else {

				//What to do if validation passes
				$update_user_role = $this->input->post();
				if ($this->Role->update_user_role($update_user_role)) {
					//We successfully updated the user so redirect
					redirect('/user_roles/index/');
				} else {
					//We failed to update the user bring us back to the page
					$header['Title'] = "User Management - Edit Role";
					$header['Heading'] = "Widget Corp User Management";
					
					$data['id'] = '';
					$data['role_name'] = '';
					$data['role_description'] = '';
					$data['active'] = '';
					
					$this->load->view('defaults/header', $header);
					$this->load->view('defaults/navigation');
					$this->load->view('defaults/sidebar');
					$this->load->view('user_roles/edit_user_role', $data);
					$this->load->view('defaults/footer');										
				}
			}
		}
		
	}
	
	public function delete_user_role ($id = NULL) {
		//Check to make sure that the function was passed a valid ID
		
		if (! empty($id) && is_numeric($id)) {
			// Valid ID passed call the delete user model function
			$delete_result = $this->Role->delete_user_role($id);
			
			if ($delete_result) {
				//Delete succeeded pass the result back
				$this->session->set_flashdata('status_message','The role was deleted successfully!');
				$this->session->set_flashdata('status_class','notice');
				redirect('/user_roles/index/');
			} else {
				//Delete failed pass the result back
				$this->session->set_flashdata('status_message','The role was not deleted successfully!');
				$this->session->set_flashdata('status_class','error');
				redirect('/user_roles/index/');
			}
		} else {
			//Invalid ID passed in so pass a message back
			$this->session->set_flashdata('status_message','The role was not deleted successfully!');
			$this->session->set_flashdata('status_class','error');
			redirect('/user_roles/index/');
		}
		
	}
	
}
/* End of file: user_roles.php
 * Location: application/controllers/user_roles.php
 */