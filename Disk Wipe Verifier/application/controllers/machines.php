<?php

class Machines extends CI_Controller {
	
	function __construct() {
		/* Call the parent constructor to make sure that the controller is fully ready */
		parent::__construct();
		$this->load->model('Machine');
		$this->load->model('Disk');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}
	
	public function index() {
		
		echo "Test";
		$this->Machine->find_all_machines_simple();
		$data['all_machines'] = $this->Machine->all_machines;

		//Get all field names from users table
		$data['headers'] = $this->db->list_fields('machines');
		
		$this->Machine->find_all_machines();
		$data['all_machines2'] = $this->Machine->all_machines;

		//Set the view variables for title & heading
		$header['Title'] = "User Management - User Listing";
		$header['Heading'] = "Widget Corp User Management";
		
		$this->load->view('defaults/header', $header);
		$this->load->view('defaults/navigation');
		$this->load->view('defaults/sidebar');
		$this->load->view('machines/index', $data);
		$this->load->view('defaults/footer');
		
		
	}
	
}
/* End of file: machines.php
 * Location: application/controllers/machines.php
 */