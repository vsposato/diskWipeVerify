<?php

class User_role extends CI_Model {
	
	var $selected_role;
	var $all_roles;
	var $all_active_roles;
	
	function __construct() {
		
		//Load the parent constructor
		parent::__construct();
		//Load the database for this function
		$this->load->database();
		
	}
	
	public function find_all_roles() {
		//Build a query that will get all roles in the table
		$query = $this->db->get('user_roles');
		//Assign results to the $all_roles variable
		$this->all_roles = $query->result_array();
		//Clear the result
		$query->free_result();
	}
	
	public function find_all_active_roles() {
		//Build a query that will select only roles that are in active state
		$query = $this->db->get_where('user_roles', array('active' => 1));
		//Assign results to $all_active_roles variable
		$this->all_active_roles = $query->result_array();
		//Clear the results
		$query->free_result();
	}
	
	public function find_role_by_id($id = "") {
		//Build a query that will select only a single role based upon the role id
		$query = $this->db->get_where('user_roles', array('id' => $id));
		//Determine that a role was found
		if ($query->num_rows() === 1) {
			//Assign the results to $selected_role
			$this->selected_role = $query->row_array();
		} else {
			//No results found so pass back false
			$this->selected_role = FALSE;
			
		}
		//Clear the results
		$query->free_result();
		
	}
	
	public function delete_user_role ($id = null) {
		
		//Confirm that the id passed is valid
		//Build the query to select the user to be deleted
		$this->db->delete('user_roles', array('id' => $id));
		//Check to see if the number of affected rows was equal to 1
		if ($this->db->affected_rows() === 1) {
			//Return true to confirm that it worked
			return TRUE;
		} else {
			//Return false to confirm that it didn't work
			return FALSE;
		}
	}
	
	public function find_role_name_by_id($id = "") {
		//Build a query that will select only a single role based upon the role id
		$query = $this->db->get_where('user_roles', array('id' => $id));
		//Determine that a role was found
		if ($query->num_rows() === 1) {
			//Assign the results to $selected_role
			$this->selected_role = $query->row_array();
			return $this->selected_role['role_name'];
		} else {
			//No results found so pass back false
			$this->selected_role = FALSE;
			return FALSE;
		}
		//Clear the results
		$query->free_result();		
	}

	public function insert_user_role ($inserted_user_role) {
		if (! empty($inserted_user_role)) {
			$data = array(
				'role_name' => $inserted_user_role['role_name'],
				'role_description' => $inserted_user_role['role_description'],
				'active' => $inserted_user_role['active']);
			$this->db->insert('user_roles', $data);
			
			if ($this->db->affected_rows() === 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}	

	public function update_user_role ($updated_user_role) {
		if (! empty($updated_user_role)) {
			$data = array(
				'role_name' => $updated_user_role['role_name'],
				'role_description' => $updated_user_role['role_description'],
				'active' => $updated_user_role['active'],
				);
			$this->db->where('id', $updated_user_role['id']);
			$this->db->update('user_roles', $data);
			
			if ($this->db->affected_rows() === 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
}