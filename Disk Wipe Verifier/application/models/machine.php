<?php

class Machine extends CI_Model {
	
	/*
	 * Let's define the class variables that will hold data that will be used
	 * by calling routines
	 */
	
	public $selected_machine = array();
	public $all_machines = array(); 
	
	function __construct() {
		/*
		 * Call the parent constructor and load the database
		 */
		
		parent::__construct();
	}
	
	public function find_machine_by_serial_simple($serial_number = NULL) {
		/*
		 * We are going to locate a machine by a given serial number
		 * and dont return any of the joined disks
		 */
		//Create the query object so that results can be handed back 
		$query = $this->db->get_where('machines', array('machine_serial' => $serial_number), 1);
		
		//Determine whether results were actuall returned
		if($query->num_rows() === 1) {
			//There was exactly one row returned which is what was expected
			//Assign the results back to the selected_machine class variable
			$this->selected_machine = $query->row_array();
		} else {
			//Something unexpected was returned so we need to hand back an error
			//Assign the null back to the selected_machine class variable
			$this->selected_machine = NULL;
		}
		
		//Clear the memory
		$query->free_result();
	}
	
	public function find_all_machines_simple() {
		/*
		 * We are going to get a listing of all machines in the database
		 * but without any of the joined disk entries
		 */
		log_message('error', 'Before query');
		
		//Create the query object showing all machines in the machine table
		$query = $this->db->get('machines');
		
		log_message('error', 'After query');
		//Determine whether the query was executed and returned results
		if($query->num_rows() > 0) {
			log_message('error', 'After if statement with === 1');
			//Results were returned so assign the result set back to the all_machines class variable
			$this->all_machines = $query->result_array();
		} else {
			log_message('error', 'After if statement with else');
			//No results were returned so assign null to the all_machines class variable
			$this->all_machines = NULL;
		}
		//Clear the memory of the query
		$query->free_result();
	}
	
	public function find_machine_by_id_simple($id = NULL) {
		/*
		 * We are going to locate a machine by its record id
		 * but without any of its joined disk entries
		 */
		
		//Create the query object showing the selected machine by id
		$query = $this->db->get_where('machines', array('id' => $id));
		
		//Determine whether the query was executed and returned only 1 record
		if($query->num_rows() === 1) {
			//Results were returned so assign the result back to the selected_machine class variable
			$this->selected_machine = $query->row_array();
		} else {
			//No results returned so assign null back to the selected_machine class variable
			$this->selected_machine = NULL;
		}
		
		//Clear the memory of the query
		$query->free_result();
	}
	
	public function find_machine_by_serial_number($serial_number = NULL) {
		/*
		 * We are going to locate a specific machine by its serial number 
		 * and join all records from the disks table for this machine
		 */
		
		//Create the select query for the machine table
		$this->db->select('*');
		$this->db->from('machines');
		$this->db->select('*');
		$this->db->from('disks');
		
		//Add the join for disks table where disks.machine_id = machines.id
		$this->db->join('disks', 'disks.machine_id = machines.id');
		
		//Add the where clause for the serial number
		$this->db->where(array('serial_number' => $serial_number));
		
		//Execute the query
		$query = $this->db->get();
		
		//Determine whether or not results were returned
		if($query->num_rows() > 0) {
			//Results were returned so assign the result back to the selected_machine class variable
			$this->selected_machine = $query->result_array();
		} else {
			//No results were returned so assign NULL to the selected machine class variable
			$this->selected_machine = NULL;
		}
		
		//Clear the memory of the query
		$query->free_result();
	}
	
	public function find_all_machines() {
		/*
		 * We are going to find all machines in the machines table
		 * and join all the records from the disks table for these machines
		 */
		
		//Create the select query for the machine table
		$this->db->select('*');
		$this->db->from('machines');
		$this->db->select('*');
		$this->db->from('disks');
		
		//Add the join for the disks table where disks.machine_id = machines.id
		$this->db->join('disks', 'disks.machine_id = machines.id');
		
		//Execute the query
		$query = $this->db->get();
		
		//Determine whether results were returned
		if($query->num_rows() > 0) {
			//Results were returned so assign the results back to the all_machines class variable
			$this->all_machines = $query->result_array();
		} else {
			//No results were returned so assign NULL to the all_machines class variable
			$this->all_machines = NULL;
		}
		
		//Clear the memory of the query
		$query->free_result();
	}
	
	public function find_machine_by_id($id = NULL) {
		/*
		 * We are going to locate a machine by its record id
		 * and join all disk entries from the disks table for this machine
		 */
		
		//Create the select query for the machine table
		$this->db->select('*');
		$this->db->from('machines');
		$this->db->select('*');
		$this->db->from('disks');
		
		//Add the join for the disks table where disks.machine_id = machines.id
		$this->db->join('disks', 'disks.machine_id = machines.id');
		
		//Add the where clause for the machine id
		$this->db->where(array('id' => $id));
		
		//Execute the query
		$query = $this->db->get();
		
		//Determine whether a single result was returned
		if($query->num_rows() > 0) {
			//Result was returned so assign the result back to the selected_machine class variable
			$this->selected_machine = $query->result_array();
		} else {
			//No result was returned so assign the NULL to the selected_machine class variable
			$this->selected_machine = NULL;
		}
		
		//Clear the memory of the query
		$query->free_result();
	}

	public function find_machines_by_sort_code($sort_code = NULL) {
		/*
		 * We are going to locate a machine by its sort code
		 * and join all disk entries from the disks table for this machine
		 */
		
		//Create the select query for the machine table
		$this->db->select('*');
		$this->db->from('machines');
		$this->db->select('*');
		$this->db->from('disks');
		
		//Add the join for the disks table where disks.machine_id = machines.id
		$this->db->join('disks', 'disks.machine_id = machines.id');
		
		//Add the where clause for the sort_code
		$this->db->where(array('sort_code' => $sort_code));
		
		//Execute the query
		$query = $this->db->get();
		
		//Determine whether a result was returned
		if($query->num_rows() > 0) {
			//Result was returned so assign the result back to the all_machines class variable
			$this->selected_machine = $query->result_array();
		} else {
			//No result was returned so assign the NULL to the all_machines class variable
			$this->selected_machine = NULL;
		}
		//Clear the memory of the query
		$query->free_result();
		
	}
	
	public function update_machine_with_disks($update_machine) {
		/*
		 * Update an associated machine record with the separate
		 * disk information for all records
		 */
		
	}
	
	public function update_machine_without_disks($updated_machine) {
		/*
		 * Update an associated machine record without the seperate disk
		 * information
		 */
	}
	
	public function insert_machine_with_disks($inserted_disk) {
		/*
		 * Insert a new machine record and pass the associated disks
		 * to the appropriate table
		 */
	}
}
/* End of file: machine.php
 * Location: application/models/machine.php
 */