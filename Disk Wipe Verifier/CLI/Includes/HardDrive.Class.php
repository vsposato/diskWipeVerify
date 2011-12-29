<?php

class HardDrive {
	
	protected $diskIdentifier = ''; //Completed
	protected $serialNumber = ''; //Completed
	protected $diskSize = ''; //Completed
	protected $partitionCount = 0; //Completed
	protected $validDisk = false; //Completed
	protected $wipeValidation = false; //Completed
	protected $singleFdiskOutput = array(); //Completed
	protected $singleHdparmOutput = array(); //Completed
	protected $singleHdparmReturn = ''; //Completed
	
	public function __construct($diskIdentifier) {
		/*
		 * This will be the construct process for the hard drive class,
		 * and we will need to get all of the items setup
		 */
		if (! $diskIdentifier ) {
			/*
			 * Failed to pass a valid disk identifier so fail
			 */
			exit;
		}
		//@TODO Need to work here - all functions are complete - make sure the process gets done
		// Set the Disk Identifier class property based upon the Disk Identifier sent into the constructor
		$this->setDiskIdentifier($diskIdentifier)
		
		// Set the Fdisk output for this hard drive instance - specifically for determining the partition counts
		$this->setSingleFdiskOutput();
		
		// Set the Hdparm output for this hard drive instance - specifically for gathering the serial number
		$this->setSingleHdparmOutput();
		
		// Determine whether or not this is a valid disk
		$this->setValidDisk();
		
		// Determine whether or not the disk wipe was successful
		
	}
	
	public function toString() {
		
	}
	
	public function getValidDisk() {
		/*
		 * Hand back the value of the validDisk class property
		 */	
		
		return $this->validDisk;
		
	}
	
	public function getWipeValidation() {
		/*
		 * Hand back the value of the wipeValidation class property
		 */	
		
		return $this->wipeValidation;
	}
	
	protected function setDiskIdentifier($diskIdentifier) {
		/*
		 * This function will set the Disk Identifier that was handed into the 
		 * class
		 */
		$this->diskIdentifier = $diskIdentifier;
	}
	
	private function _cleanHDIdentification($HDIdentification) {
		//This function will take the model, firmware and serial and return them without
		//the beginning entry
		
		//The standard is an = sign so break out from there
		$temp_hd_info = explode('=',$HDIdentification);
		
		//Return the 2nd index of the array returned which will hold the important information
		return $temp_hd_info[1];
	}
	
	private function _cleanHDSize($HDSize) {
		//This function will remove the , from the sizing information returned
		//from the fdisk function
		
		//The standard separator for the hard drive size is a comma
		$temp_hd_size = explode(',', $HDSize);
		
		//Return the 1st array entry as this will house the friendly size
		return $temp_hd_size[0];
	}
	
	protected function setSingleFdiskOutput() {
		/*
		 * Using a single disk identifier create the fdisk output
		 * for the creation of the data for this single hard drive
		 */
		$command = "sudo fdisk {$this->diskIdentifier} -l | grep -e \"^/\"";
		
		exec($command, $this->singleFdiskOutput);
	}
	
	protected function setSingleHdparmOutput() {
		/*
		 * This function will generate the Hard Drive parameter information
		 * for this hard drive instance
		 */
		
		// Create the hdparm command to be executed
		$command = "sudo hdparm -i {$this->diskIdentifier} | grep -e SerialNo=";
		
		// Execute the command passing the output and return to class properties
		exec($command, $this->singleHdparmOutput,$this->singleHdparmReturn);
	}

	protected function setPartitionCount() {
		/*
		 * This function will use the fdisk output generated previously
		 * and determine how many partitions are active on the current 
		 * drive
		 */
		
		/*
		 * We commented out the following 30 lines on 12/28/11 to account for a new way 
		 * of generating fdisk output with grep commands
		 */
		/*$partitionCounter = 0;
		
		foreach ($fdisk_output as $data) {
			/*
			 * Let's work through each individual line looking for disk
			 * entries or the partition runs
			 */
			
			/*
			 * First we determine whether or not this is the Disk entry portion of the 
			 * fdisk output.
			 */
			/*$disk_entry = stripos($data, "Disk /");
			if ($disk_entry === FALSE) {
				
				//We did not find any disk entry so we are going to look for a partition entry
				$part_entry = stripos($data, "/dev/");
				
				if ($part_entry !== FALSE) {
					//We did find a partition entry so we are going to increment the partition count
					$partitionCounter++;
				}

			} elseif ($disk_entry !== FALSE) {
				//This is a physical hard drive not a partition so just continue
				continue;
			}
		}
		
		$this->partitionCount = $partitionCounter;*/
		
		//Since we are grepping out only the lines that have partition information then any additions to the array
		//are partitions
		$this->partitionCount = count($this->singleFdiskOutput);
		
	}	

	protected function setValidDisk() {
		/*
		 * This function will utilize the hdparm command to determine
		 * if the drive in question is a valid disk or not. A non-valid
		 * disk (i.e., USB, CD) will pass back an error when the hdparm
		 * command is run on it. This will allow us to identify whether
		 * or not the drive is valid
		 */

		/*
		 * We commented out the following 25 lines on 12/28/11 to account for a new way 
		 * of generating hdparm output with grep commands
		 */
		
		/*
		//Build the actual shell command
		$exec_command = "sudo hdparm -i {$this->diskIdentifier}";
		//Create the disk id variable
		$disk_id = substr($disk,(strlen($disk) - 3),3);
		//Create the dynamic variable for the hdparm output
		$hdparm_output = $disk_id . "_hdparm_output";
		//Create the dynamic variable for the hdparm return
		$hdparm_return = $disk_id . "_return";
		
		//Execute the command
		exec($exec_command, $$hdparm_output, $$hdparm_return);
		
		if ($$hdparm_return == 0) {
			/*
			 * HDPARM returned a value so this is a valid disk to be reviewed
			 */
			/*$this->validDisk = true;
		} else {
			/*
			 * HDPARM returned an error therefore it is most likely a USB / CD bootable disk
			 * so we will make this a non-valid disk for purpose of disk wipe
			 */
			/*$this->validDisk = false;
		}*/

		if (! isset($this->singleHdparmOutput)) {
			
			//No hdparm output detected so call the method to generate it
			$this->setSingleHdparmOutput();
		
		}
		
		if ($this->singleHdparmReturn == 0) {
			/*
			 * HDPARM returned a value so this is a valid disk to be reviewed
			 */
			$this->validDisk = true;
		} else {
			/*
			 * HDPARM returned an error therefore it is most likely a USB / CD bootable disk
			 * so we will make this a non-valid disk for purpose of disk wipe
			 */
			$this->validDisk = false;
		}
	}
	
	protected function setDiskSerialNumber() {
		/*
		 * This function will utilize the hdparm command to gather the
		 * serial information from the physical disk and return it.
		 */

		/*
		 * We commented out the following 29 lines on 12/28/11 to account for a new way 
		 * of generating hdparm output with grep commands
		 */
		/*
		//Build the actual shell command
		$exec_command = "sudo hdparm -i {$this->diskIdentifier}";
		//Create the disk id variable
		$disk_id = substr($this->diskIdentifier,(strlen($this->diskIdentifier) - 3),3);
		//Create the dynamic variable for the hdparm output
		$hdparm_output = $disk_id . "_hdparm_output";
		//Create the dynamic variable for the hdparm return
		$hdparm_return = $disk_id . "_return";
		
		//Execute the command
		exec($exec_command, $$hdparm_output, $$hdparm_return);
		
		if ($$hdparm_return == 0) {

			//Return value of 0 means a successful run - so process the output
			foreach($$hdparm_output as $data) {

				//Is this the line that has the data we are looking for - it should begin with Model
				$identification_entry = strpos($data, 'Model=');
				
				if ($identification_entry !== FALSE) {

					//This is the line we need
					//Separate the model, firmware, and serial number into an array
					$identification = explode(',', $data);
					$this->serialNumber = $this->_cleanHDIdentification($identification[2]);
				}
			} 
		}*/ 
		
		// Set a temporary array to hold the exploded identification line
		$identification = explode(",",$this->singleHdparmOutput);
		
		// After the explode the Serial Number is stored in the 3rd array index
		$this->serialNumber = $this->_cleanHDIdentification($identification[2]);
	}
	
	protected function setWipeValidation() {
		/*
		 * This function will determine the disk wipe status based upon the partition count class property.
		 * A hard drive that has been wiped should have 0 partitions. Any partition count other than 0,
		 * will result in a negative disk wipe status
		 */
		
		// Check the partition count to determine if it is 0
		if ($this->partitionCount === 0) {
			
			// There are no partitions so this is a successful wipe
			$this->wipeValidation = true;
			
		}
	}
	protected function setDiskSize() {
		/*
		 * This function will use the fdisk output to parse through and 
		 * find the line that contains the size of the disk
		 */
		
		foreach ($this->singleFdiskOutput as $data) {
			/*
			 * Let's work through each individual line looking for disk
			 * entries or the partition runs
			 */
			$disk_entry = stripos($data, "Disk /");
			if ($disk_entry === FALSE) {
				/*
				 * Not a physical disk entry so we can simply continue on to the next line
				 */
				continue;
			} elseif ($disk_entry !== FALSE) {
				//We found the Disk string inside of the data line so we are going to break it down
				//Build temporary string to get the 
				//$temp_disk = substr($data, $disk_entry, 13);
				$temp_disk = explode(':', $data);
				$this->diskSize = _cleanHDSize($temp_disk[1]);
			}
		}
	}
	
	
}