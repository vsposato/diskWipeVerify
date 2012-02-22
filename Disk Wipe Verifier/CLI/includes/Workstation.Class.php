<?php

require_once('Machine.Class.php');

class Workstation extends Machine {

	/*
	 * This class will be for workstations and are being separated out to account for major differences in 
	 * the functions available between the server and the workstation. 
	 * 
	 * This function will differ from the parent class in it's handling of the hard drives specifically.
	 * 
	 */
	
	public function __construct() {
		parent::__construct();
		
		// Import the Hard Drive class for Workstations
		require_once('WorkstationHardDrive.Class.php');
		
		// We will now get the hard drive information and create new hard drives for each drive in this machine
		$this->findHardDrives();
		
		writeToLogFile("Workstation Class ", "Finished Finding Hard Drives", $this->logFile);
		
		// We are now going to determine if all valid hard drives have been wiped and therefore the machine has passed
		$this->determineWipeStatus();
		
	}
	
	protected function setHardDriveCount() {
		/*
		 * We will be determining the number of functional hard drives within the
		* machine that are NOT the bootable verify drive
		*/
	
		writeToLogFile("Workstation Class ", "setHardDriveCount - begin", $this->logFile);
	
		// We need to make sure that the fdisk array has already been setup, and if not we will run the function to set it up
		if (empty($this->fdiskOutput)) {
			/*
			 * There currently is nothing in the fdisk output array so we will need to call the function
			* to create the data
			*/
				
			writeToLogFile("Workstation Class ", "setHardDriveCount - inside if fdiskOutput", $this->logFile);
				
			$this->_fdiskOutputCreation();
		}
	
		// We are going to use the count of the fdiskOutput array to give us the number of hard drives
		// since the exec command that we used was specific to lines that output harddrives
		writeToLogFile("Workstation Class ", "setHardDriveCount - after if hardDriveCount={$this->hardDriveCount}", $this->logFile);
	
		$this->hardDriveCount = count($this->fdiskOutput);
	
		writeToLogFile("Workstation Class ", "setHardDriveCount - after assignment hardDriveCount={$this->hardDriveCount}", $this->logFile);
	
		/*
		 * If the hardDriveCount is 1 AND the liveCD is false, then we have encountered a drilling situation, also if the hardDriveCount is 0
		* AND the liveCD is true we have encountered a drilling situation
		*/
		if (($this->hardDriveCount === 1) && ($this->getLiveCD() === false)) {
			/*
			 * We found only one hard drive so we need to set the drill status class property
			*/
			writeToLogFile("Workstation Class ", "setHardDriveCount - inside if for DrillStatus hardDriveCount={$this->hardDriveCount}", $this->logFile);
				
			$this->setDrillStatus();
	
		} elseif (($this->hardDriveCount === 0) && ($this->getLiveCD() === true)) {
				
			writeToLogFile("Workstation Class ", "setHardDriveCount - inside if for DrillStatus hardDriveCount={$this->hardDriveCount}", $this->logFile);
				
			$this->setDrillStatus();
				
		}
	}
	
	protected function createDrilledHardDrives() {
		/*
		 * This function will generate the hard drives for the machine with the drill flag set for each of them
		*/
	
		/*
		 * This is a workstation so we will need to generate 1 hard drive for drilling
		*/
			
		//Since there is only one hard drive just pass string literals to the function
		$this->hardDrives["/dev/sda"] = new HardDrive("/dev/sda",$this->drillStatus);
	}
	
	protected function createHardDriveInstances() {
		/*
		 * This function is going to read the fdiskOutput and add an array entry that points to a new instance
		* of the hard drive class
		*/
	
		writeToLogFile("Workstation Class ", "createHardDriveInstances - begin", $this->logFile);
	
		if ($this->drillStatus === false) {
			//We didn't drill the hard drives so continue the process
			foreach ($this->fdiskOutput as $disk) {
				// We are going to process each row of disk data to create new hard drive instances
				$tempHDIdentifier = $this->_cleanFdiskLine($disk);
	
				writeToLogFile("Workstation Class ", "createHardDriveInstances - inside foreach tempHDIdentifier={$tempHDIdentifier}", $this->logFile);
	
				// We are going to now create a new instance of a hard drive and assign it to my class property
				$this->hardDrives[$tempHDIdentifier] = new WorkstationHardDrive($tempHDIdentifier);
			}
		}
	}
	
	
	
}

/*
* End of File: Workstation.Class.php
* Class: Workstation
* File: ./includes/Workstation.Class.php
*/