<?php

class User extends CI_Model {

	public $all_users = array();
	public $selected_user = array();
	
	function __construct() {
		/* Call the parent constructor to make sure that the model is fully ready */
		parent::__construct();
		//Load the database for this function
		$this->load->database();
	}
	
	public function login_user ($email_address = null, $password = null) {
		//This function will find a user and confirm the login is valid, and then it will set the session variables to support the user
		
		//Determine if username and password have been passed in validly
		if (! empty($email_address) && ! empty($password)) {
			//Parameters are valid, so now let's send a query to the database with user's credentials
			$query = $this->db->get_where('users', array('email_address' => $email_address, 'password' => $password), 1);	
			//Check to determine that a single user was returned
			if ($query->num_rows() === 1) {
				//Single user passed back
				$this->selected_user = $query->row_array();
				//Set user ID, email address, and role to the session variables
				$this->session->set_userdata('id', $this->selected_user['id']);
				$this->session->set_userdata('email_address', $this->selected_user['email_address']);
				$this->session->set_userdata('first_name', $this->selected_user['first_name']);
				$this->session->set_userdata('user_role_id', $this->selected_user['user_role_id']);
				$this->session->set_userdata('logged_in', TRUE);
				//Tell calling routine that we succeeded
				$this->session->set_flashdata('status_class', 'notice');
				$this->session->set_flashdata('status_message', 'You have been logged in successfully!');
				return TRUE;		
			} else {
				//Something other than single user passed back
				//Tell calling routine that we failed
				$this->session->set_flashdata('status_class', 'error');
				$this->session->set_flashdata('status_message', 'Email address or password did not match. Please try again!');
				return FALSE;	
			}
		} else {
			//Parameters are not valid so tell the calling routine so
			$this->session->set_flashdata('status_class', 'error');
			$this->session->set_flashdata('status_message', 'Email address or password were invalid. Please try again!');
			return FALSE;			
		}
		
	}
	
	public function find_all_users() {
		//Build query string here using active record pattern
		//SELECT * from users;
		$query = $this->db->get('users');
		
		$this->all_users = $query->result_array();
		
		$query->free_result();		
	}
	
	public function find_user_by_id($id = null) {
		//Build the query to select the user
		$query = $this->db->get_where('users', array('id' => $id), 1);
		//Check to see that only 1 result was returned
		if ($query->num_rows() === 1) {
			//Assign the results to the instance variable
			$this->selected_user = $query->row_array();
		} else {
			//No results returned
			$this->selected_user = NULL;
		}
		//Clear the results
		$query->free_result();
	}
	
	public function update_user ($updated_user) {

		if (! empty($updated_user)) {
			if (empty($updated_user['password'])) {
				$data = array(
					'last_name' => $updated_user['last_name'],
					'first_name' => $updated_user['first_name'],
					'email_address' => $updated_user['email_address'],
					'user_role_id' => $updated_user['user_role_id']);
			} else {
				$data = array(
					'last_name' => $updated_user['last_name'],
					'first_name' => $updated_user['first_name'],
					'email_address' => $updated_user['email_address'],
					'password' => $updated_user['password'],
					'user_role_id' => $updated_user['user_role_id']);
			}
			$this->db->where('id', $updated_user['id']);
			$this->db->update('users', $data);
			
			if ($this->db->affected_rows() === 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	
	public function insert_user ($inserted_user) {
		log_message('error', 'Before 1st logic test');
		if (! empty($inserted_user)) {
			$data = array(
				'last_name' => $inserted_user['last_name'],
				'first_name' => $inserted_user['first_name'],
				'email_address' => $inserted_user['email_address'],
				'password' => $inserted_user['password'],
				'user_role_id' => $inserted_user['user_role_id']);
			$this->db->insert('users', $data);

			log_message('error', "Before 2nd logic test");

			if ($this->db->affected_rows() === 1) {

				log_message('error', "Should be true");
				return TRUE;

			} else {

				log_message('error', "Should be false for affected rows");
				return FALSE;

			}

		} else {

			log_message('error', "Should be empty data array");
			return FALSE;

		}
	}

	public function delete_user ($id = null) {
		
		//Confirm that the id passed is valid
		//Build the query to select the user to be deleted
		$this->db->delete('users', array('id' => $id));
		//Check to see if the number of affected rows was equal to 1
		if ($this->db->affected_rows() === 1) {
			//Return true to confirm that it worked
			return TRUE;
		} else {
			//Return false to confirm that it didn't work
			return FALSE;
		}
	}
}

/* End of file: user.php
 * Location: application/models/user.php
 */