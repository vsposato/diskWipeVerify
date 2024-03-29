<?php

class Machine {
	/*
	 * This class is the base class of all computers that are going to 
	 * be verified by this program
	 * 
	 */
	
	protected $serialNumber = '';
	protected $serialNumberType = '';
	protected $hardDriveCount = 0;
	protected $validDriveCount = 0;
	protected $hardDrives = array();
	protected $fdiskOutput = array();
	protected $diskWipeStatus = 'FAILED';
	protected $hostname = '';
	protected $liveCD = false;
	protected $drillStatus = false;
	protected $logFile = '';
	
	public function __construct() {
		/*
		 * This will create a new instance of a machine and build the
		 * class properities that can be filled in
		 */
		
		/* 
		 * Define global variables required to perform functions
		 */
		global $logFile;
		
		$this->logFile = $logFile;
				
		writeToLogFile("Machine Class ", "Instantiate", $this->logFile);
		
		/* Define STDIN in case it wasn't defined somewhere else */
		if (! defined("STDIN")) {
			define("STDIN", fopen('php://stdin','r'));
		}
		
		// Import the Hard Drive class
		require_once('HardDrive.Class.php');
		
		// We will need to set the serial number of the machine
		$this->setSerialNumber();
		
		// We will need to determine the system verification type - either livecd or liveusb
		$this->setLiveCD();
		
		// We will now get the hard drive information and create new hard drives for each drive in this machine
		$this->findHardDrives();

		writeToLogFile("Machine Class ", "Finished Finding Hard Drives", $this->logFile);
		
		// We are now going to determine if all valid hard drives have been wiped and therefore the machine has passed
		$this->determineWipeStatus();
	}
	
	public function getDiskWipeStatus() {
		/*
		 * This will return the Disk Wipe Status of the machine
		 */		
		
		return $this->diskWipeStatus;
	}
	
	public function getHardDrives() {
		/*
		 * This function will iterate through all hard drives in the hardDrives array
		 * and return a well-formed array of the valid hard drives. By valid we are referring
		 * to hard drives that are not part of the verification system
		 */
		
		// Create temporary array to be returned
		$tempHardDriveArray = array();
		
		foreach ($this->hardDrives as $disk) {
			if ($disk->getValidDisk()) {
				//This is a valid disk so build the array
				
				//Add the serial number
				$tempHardDriveArray[$disk->getDiskID()]["serial"] = $disk->getSerialNumber();
				
				//Add the wipemethod
				$tempHardDriveArray[$disk->getDiskID()]["wipemethod"] = $disk->getWipeMethod();
				
				//Add the wipe status
				$tempHardDriveArray[$disk->getDiskID()]["wipestatus"] = ($disk->getWipeValidation()) ? 'WIPED' : 'FAILED';
				
			} else {
				
				//This disk belongs to the verification system or is USB so ignore it
				continue;
			
			}
		}
		
		// Pass the array back to the calling function
		return $tempHardDriveArray;
	}
	
	public function getSiteCode() {
		/*
		 * This function will return the site code entered by the user
		 */
		
		return $this->siteCode;
	}
	
	public function getLiveCD() {
		/*
		 * This function will return the value of the liveCD class property
		 */
		
		return $this->liveCD;
		
	}
	
	public function getSerialNumber() {
		/*
		 * This function will return the serial number found by the 
		 */
		
		return $this->serialNumber;
	}

	public function getSerialNumberType() {
		/*
		 * This function will return where the serial number came from using the 
		 * serialNumberType class property
		 */	
		
		return $this->serialNumberType;
	}
	
	public function getValidDriveCount() {
		/*
		 * This function will return the class property validDriveCount
		 */
		
		return $this->validDriveCount;
	}
	
	public function getDiskWipeMethod() {
		/*
		 * This function will use the $drillStatus class property to determine if we drilled
		 * or gdisked the machine
		 */
		
		if ($this->drillStatus) {
			// We drilled this machine so return DRILLED
			return 'DRILLED';
			
		} elseif (! $this->drillStatus) {
			//We gdisked this machine so return GDISKED
			return 'GDISKED';
		}
	}
	
	protected function setHostname() {
		/*
		 * This function will use the uname function to gather the hostname of the verification system
		 * and then set the class property with it
		 */
		
		// Execute uname function and hand off return data to tempHostname
		exec('uname -n', $tempHostname);
		
		// Since the return should only be 1 element then we will shift off the only value to the class property
		$this->hostname = array_shift($tempHostname);
	}
	
	protected function setLiveCD() {
		/*
		 * This function will take the host name of this machine and validate whether we are using a 
		 * live CD or a live USB drive.
		 */
		
		// Check to make sure the hostname is populated
		if (empty($this->hostname)) {
			
			// It shouldn't be empty, so call the setting method
			$this->setHostname();
		}
		
		// Test the hostname to see whether it is a liveCD or a liveUSB
		if ($this->hostname === 'diskwipecd') {
			
			// The hostname for a live cd is diskwipecd so set liveCD to true
			$this->liveCD = true;
			
		} elseif ($this->hostname === 'diskwipeusb') {
			
			// The hostname for a live usb is diskwipeusb so set liveCD to false
			$this->liveCD = false;
			
		} else {

			// The host name is neither the defaults for a liveUSB or liveCD so we are going to assume that 
			// this is some other situation like testing
			$this->liveCD = true;
	
		}
		
	}
	
	protected function setSerialNumber() {
		/*
		 * This function will gather the 3 BIOS available serial numbers and then compare to determine
		 * which serial number is the most accurate
		 */	
		
		// Create the temporary variables to hold the 3 serial numbers
		$chassisSN = $this->_getChassisSerialNumber();
		$baseboardSN = $this->_getBaseboardSerialNumber();
		$systemSN = $this->_getSystemSerialNumber();
		
		if (($chassisSN == $baseboardSN) || ($chassisSN == $systemSN)) {
			//Chassis serial number matches one of the other 2 serial numbers - let's use that
			$this->serialNumber = $chassisSN;
			$this->serialNumberType = 'chassis';
			
		} else {
			//Chassis serial number did not match either of the other 2 so test to see if system serial does
			if (($systemSN == $baseboardSN)) {
				//System serial number matches baseboard so we will use that serial number
				$this->serialNumber = $systemSN;
				$this->serialNumberType = 'system';
			} else {
				//System serial number didn't match either but it is the most reliable so use it
				$this->serialNumber = $systemSN;
				$this->serialNumberType = 'default';
			}
		}		
		
	}
	
	protected function setValidDriveCount() {
		/*
		 * This function will iterate through the hard drives in this machine and count the
		 * number of drives that are valid (not belonging to the verification system)
		 */
		
		// Create a temporary variable to hold the counter
		$tempValidCounter = 0;
		
		// Iterate through all defined drives
		foreach ($this->hardDrives as $disk) {
			
			//Check to see if the disk is valid
			if ($disk->getValidDisk()) {
				//Disk is valid so increment the counter
				$tempValidCounter++;
			}
		}
		
		//Assign temporary counter to the class property
		$this->validDriveCount = $tempValidCounter;
	}
			
	protected function setHardDriveCount() {
		/*
		 * We will be determining the number of functional hard drives within the 
		 * machine that are NOT the bootable verify drive
		 */
		
		writeToLogFile("Machine Class ", "setHardDriveCount - begin", $this->logFile);
		
		// We need to make sure that the fdisk array has already been setup, and if not we will run the function to set it up
		if (empty($this->fdiskOutput)) {
			/*
			 * There currently is nothing in the fdisk output array so we will need to call the function
			 * to create the data
			 */
			
			writeToLogFile("Machine Class ", "setHardDriveCount - inside if fdiskOutput", $this->logFile);
			
			$this->_fdiskOutputCreation();
		}
		
		// We are going to use the count of the fdiskOutput array to give us the number of hard drives
		// since the exec command that we used was specific to lines that output harddrives
		writeToLogFile("Machine Class ", "setHardDriveCount - after if hardDriveCount={$this->hardDriveCount}", $this->logFile);
		
		$this->hardDriveCount = count($this->fdiskOutput);

		writeToLogFile("Machine Class ", "setHardDriveCount - after assignment hardDriveCount={$this->hardDriveCount}", $this->logFile);
		
		/*
		 * If the hardDriveCount is 1 AND the liveCD is false, then we have encountered a drilling situation, also if the hardDriveCount is 0
		 * AND the liveCD is true we have encountered a drilling situation
		 */
		if (($this->hardDriveCount === 1) && ($this->getLiveCD() === false)) {
			/*
			 * We found only one hard drive so we need to set the drill status class property
			 */
			writeToLogFile("Machine Class ", "setHardDriveCount - inside if for DrillStatus hardDriveCount={$this->hardDriveCount}", $this->logFile);
			
			$this->setDrillStatus();

		} elseif (($this->hardDriveCount === 0) && ($this->getLiveCD() === true)) {
			
			writeToLogFile("Machine Class ", "setHardDriveCount - inside if for DrillStatus hardDriveCount={$this->hardDriveCount}", $this->logFile);
			
			$this->setDrillStatus();
			
		}
	}
	
	protected function setDrillStatus() {
		/*
		 * This function will determine if this is a true drill situation, and then create
		 * the number of hard drives required
		 */
		
		echo "We only detected one hard drive, did you drill the drives for this machine? (Y / N) \n";
		do {
			//Loop until the user enters a y or a n to determine if the hard drive was drilled
			do {
				//Get a single character from the STDIN
				$answer = fgetc(STDIN);
			} while ( trim($answer) == '');
		} while (strtoupper($answer) != 'Y' && strtoupper($answer) !='N');
	
		// Test to determine if this is a drill situation
		if (strtoupper($answer) == 'Y') {
			// The user says this is a drill situation so we are going set the status to true
			$this->drillStatus = true;
			
			// Now we are going to call the drilled hard drives creator
			$this->createDrilledHardDrives();
		} else {
			// The user says this is not a drill situation so kill the program because something is wrong
			die('Shutdown the machine, check all cable connections, make sure hard drive is functioning, and re-run this process!');
		}
	}
	
	protected function findHardDrives() {
		/*
		 * This function will find all of the active hard drives within the machine
		 * and set the counter
		 */
		
		// We are going to call the function that gets a hard drive count using basic linux commandline functions
		$this->setHardDriveCount();
		
		// We are going to process the fdiskOutput array to create a new hardDrive class for each hard drive in the machine
		$this->createHardDriveInstances();

		// Now we have built the hard drives so we can count the valid hard drives
		$this->setValidDriveCount();
				
	}
	
	protected function determineWipeStatus() {
		/*
		 * This function will iterate through all hard drives in the machine to determine
		 * are they A) valid and B) wiped. If so it will alter the status to make the Disk Wipe Status true.
		 */
		
		// Set the variable that will be changed if a hard drive failed
		$anyHardDriveFailed = false;
		
		// Iterate through each hard drive instance defined in the machine
		foreach ($this->hardDrives as $hardDrive) {
			
			// Check to see if this drive is both valid and wiped
			if ($hardDrive->getValidDisk() && $hardDrive->getWipeValidation()) {
				
				//Disk is both valid and wiped so continue throught the loop
				continue;
				
			} elseif ($hardDrive->getValidDisk() && ! $hardDrive->getWipeValidation()) {
				
				// Hard drive was a valid drive but was not wiped so we change the flag to be a fail
				$anyHardDriveFailed = true;
			
			}
		}

		// Check to see if we made it through all iterations without a failed drive, if so change the machine verification to true
		if (! $anyHardDriveFailed) {
			$this->diskWipeStatus = 'PASSED';
		}
	}
	
	protected function createDrilledHardDrives() {
		/*
		 * This function will determine if this is a workstation or a server, and then generate 
		 * the count of hard drives for that machine with the drill flag set for each of them
		 */
		
		echo "Is this machine a [S]erver or a [W]orkstation? (S / W) \n";
		do {
			//Loop until the user enters a y or a n to determine if the hard drive was drilled
			do {
				//Get a single character from the STDIN
				$answer = fgetc(STDIN);
			} while ( trim($answer) == '');
		} while (strtoupper($answer) != 'S' && strtoupper($answer) !='W');
		
		if (strtoupper($answer) == 'S') {
			/*
			 * This is a server so we will need to generate 4 hard drives for drilling
			 */
			
			
			//Build an array for the disk identifiers to be used for the hard drives
			$serverDiskIdentifiers = array("/dev/sda","/dev/sdb","/dev/sdc","/dev/sdd");
			
			foreach ($serverDiskIdentifiers as $disk) {
				
				//Create a new instance for each of the hard drives that were drilled
				$this->hardDrives[$disk] = new HardDrive($disk,$this->drillStatus);
			}
		} else {
			/*
			 * This is a workstation so we will need to generate 1 hard drive for drilling
			 */
			
			//Since there is only one hard drive just pass string literals to the function
			$this->hardDrives["/dev/sda"] = new HardDrive("/dev/sda",$this->drillStatus);
		}
	}
		
	protected function createHardDriveInstances() {
		/*
		 * This function is going to read the fdiskOutput and add an array entry that points to a new instance 
		 * of the hard drive class
		 */	
		
		writeToLogFile("Machine Class ", "createHardDriveInstances - begin", $this->logFile);
		
		if ($this->drillStatus === false) {
			//We didn't drill the hard drives so continue the process
			foreach ($this->fdiskOutput as $disk) {
				// We are going to process each row of disk data to create new hard drive instances
				$tempHDIdentifier = $this->_cleanFdiskLine($disk);
				
				writeToLogFile("Machine Class ", "createHardDriveInstances - inside foreach tempHDIdentifier={$tempHDIdentifier}", $this->logFile);
				
				// We are going to now create a new instance of a hard drive and assign it to my class property
				$this->hardDrives[$tempHDIdentifier] = new HardDrive($tempHDIdentifier);
			}
		}
	}
		
	private function _cleanFdiskLine($fdiskInput) {
		/*
		 * This function will take a single line of output from fdisk and break it down to return the 
		 * linux disk identifier
		 */
		
		// Sample Fdisk Output line: Disk /dev/sda: 80.0 GB, 80026361856 bytes
		// We need to break @ the : and then strip off the leading Disk and space
		
		// Separate the line into 2 separate pieces at the :
		$tempFdiskInput = explode(":", $fdiskInput);
		
		// Take the first part - array index 0 and take the leftmost characters based on length of string - 5 ("Disk " is 5 characters)
		$tempSingleInput = substr($tempFdiskInput[0],5);

		return $tempSingleInput;
	}
		
	private function _fdiskOutputCreation() {
		/*
		 * This function will use system commands to output the physical
		 * disk information for us throughout many of our functions
		 */
		exec('sudo fdisk -l | grep -e "^Disk /"', $this->fdiskOutput);	
	}

	private function _getSystemSerialNumber() {
		/*
		 * Here we will use the exec command to pull the system serial number and then
		 * hand it back to the calling function
		 */
		
		 exec('sudo dmidecode -s system-serial-number', $tempSerialNumber);
		 
		 //Shift the first array item off to the return value
		 return array_shift($tempSerialNumber);
	}
	
	private function _getBaseboardSerialNumber() {
		/*
		 * Here we will use the exec command to pull the baseboard serial number and then
		 * hand it back to the calling function
		 */
		
		 exec('sudo dmidecode -s baseboard-serial-number', $tempSerialNumber);
		 
		 //Shift the first array item off to the return value
		 return array_shift($tempSerialNumber);
		
	}
	
	private function _getChassisSerialNumber() {
		/*
		 * Here we will use the exec command to pull the chassis serial number and then
		 * hand it back to the calling function
		 */
		
		 exec('sudo dmidecode -s chassis-serial-number', $tempSerialNumber);
		 
		 //Shift the first array item off to the return value
		 return array_shift($tempSerialNumber);
	}

}

/*
 * End of File: Machine.Class.php
 * Class: Machine
 * File: ./includes/Machine.Class.php
 */