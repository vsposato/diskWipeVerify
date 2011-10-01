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
		
		$this->load->database();
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
		
		//Create the query object showing all machines in the machine table
		
		//Determine whether the query was executed and returned results
		
		//Results were returned so assign the result set back to the all_machines class variable
		
		//No results were returned so assign null to the all_machines class variable
		
		//Clear the memory of the query
	}
	
	public function find_machine_by_id_simple($id = NULL) {
		/*
		 * We are going to locate a machine by its record id
		 * but without any of its joined disk entries
		 */
		
		//Create the query object showing the selected machine by id
		
		//Determine whether the query was executed and returned only 1 record
		
		//Results were returned so assign the result back to the selected_machine class variable
		
		//No results returned so assign null back to the selected_machine class variable
		
		//Clear the memory of the query
		
	}
	
	public function find_machine_by_serial_number($serial_number = NULL) {
		/*
		 * We are going to locate a specific machine by its serial number 
		 * and join all records from the disks table for this machine
		 */
		
		//Create the select query for the machine table
		
		//Add the join for disks table where disks.machine_id = machines.id
		
		//Add the where clause for the serial number
		
		//Execute the query
		
		//Determine whether or not results were returned
		
		//Results were returned so assign the result back to the selected_machine class variable
		
		//No results were returned so assign NULL to the selected machine class variable
		
		//Clear the memory of the query
	}
	
	public function find_all_machines() {
		/*
		 * We are going to find all machines in the machines table
		 * and join all the records from the disks table for these machines
		 */
		
		//Create the select query for the machine table
		
		//Add the join for the disks table where disks.machine_id = machines.id
		
		//Execute the query
		
		//Determine whether results were returned
		
		//Results were returned so assign the results back to the all_machines class variable
		
		//No results were returned so assign NULL to the all_machines class variable
		
		//Clear the memory of the query
	}
	
	public function find_machine_by_id($id = NULL) {
		/*
		 * We are going to locate a machine by its record id
		 * and join all disk entries from the disks table for this machine
		 */
		
		//Create the select query for the machine table
		
		//Add the join for the disks table where disks.machine_id = machines.id
		
		//Execute the query
		
		//Determine whether a single result was returned
		
		//Result was returned so assign the result back to the selected_machine class variable
		
		//No result was returned so assign the NULL to the selected_machine class variable
		
		//Clear the memory of the query
	}
}
/* End of file: machine.php
 * Location: application/models/machine.php
 */