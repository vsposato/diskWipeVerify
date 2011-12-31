<?php

class HardDrive {
	
	protected $diskIdentifier = ''; //Completed
	protected $serialNumber = ''; //Completed
	protected $diskSize = ''; //Completed
	protected $partitionCount = 0; //Completed
	protected $validDisk = false; //Completed
	protected $wipeMethod = 'gdisk';
	protected $wipeValidation = false; //Completed
	protected $singleFdiskOutput = array(); //Completed
	protected $singleHdparmOutput = array(); //Completed
	protected $singleHdparmReturn = ''; //Completed
	
	public function __construct($diskIdentifier, $drill = false) {
		/*
		 * This will be the construct process for the hard drive class,
		 * and we will need to get all of the items setup
		 */

		/* 
		 * Define global variables required to perform functions
		 */
		global $logFile;
		
		WriteToLogFile("Hard Drive Class ", "Instantiate", $logFile);
		
		/* Define STDIN in case it wasn't defined somewhere else */
		if (! defined("STDIN")) {
			define("STDIN", fopen('php://stdin','r'));
		}
		
		if (! $diskIdentifier ) {
			/*
			 * Failed to pass a valid disk identifier so fail
			 */
			die ('Something happened - no disk identifier passed to the hard drive creator!');
		}
		
		// Set the Disk Identifier class property based upon the Disk Identifier sent into the constructor
		$this->setDiskIdentifier($diskIdentifier);
		
		// Set the wipeMethod class property for this hard drive
		$this->setWipeMethod($drill);
		
		// Set the Fdisk output for this hard drive instance - specifically for determining the partition counts
		$this->setSingleFdiskOutput();
		
		// Set the Hdparm output for this hard drive instance - specifically for gathering the serial number
		$this->setSingleHdparmOutput();
		
		// Set the hard drive serial number
		$this->setDiskSerialNumber();
		
		// Set the partition count for this hard drive
		$this->setPartitionCount();
		
		// Determine whether or not this is a valid disk
		$this->setValidDisk();
		
		// Determined if the disk passes disk wipe validation
		$this->setWipeValidation();
	}
	
	public function getDiskID() {
		/*
		 * This function returns the disk identifier for this hard drive
		 */	
		return $this->diskIdentifier;
		
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
	
	public function getSerialNumber() {
		/*
		 * This function returns the serial number of the hard drive
		 */
		
		return $this->serialNumber;
	}
	
	public function getWipeMethod() {
		/*
		 * This function returns the method used to wipe the hard drive
		 */
		
		return $this->wipeMethod;
	}
	
	protected function setWipeMethod($drill = false) {
		/*
		 * This function will set the wipe method of the hard drive instance
		 */
		writeToLogFile("Hard Drive Class ", "setWipeMethod - begin - drill={$drill} wipeMethod={$this->wipeMethod}", $logFile);
		
		if ($drill) {

			writeToLogFile("Hard Drive Class ", "setWipeMethod - inside begin true drill if - drill={$drill} wipeMethod={$this->wipeMethod}", $logFile);
			
			// The drill flag is on so mark the hard drive as drilled
			$this->wipeMethod = 'drill';

			writeToLogFile("Hard Drive Class ", "setWipeMethod - inside end true drill if - drill={$drill} wipeMethod={$this->wipeMethod}", $logFile);
		} else {
			
			writeToLogFile("Hard Drive Class ", "setWipeMethod - inside begin false drill if - drill={$drill} wipeMethod={$this->wipeMethod}", $logFile);

			// The drill flag is off so mark the hard drive as gdisked (the default)
			$this->wipeMethod = 'gdisk';

			writeToLogFile("Hard Drive Class ", "setWipeMethod - inside end false drill if - drill={$drill} wipeMethod={$this->wipeMethod}", $logFile);
		}
		writeToLogFile("Hard Drive Class ", "setWipeMethod - end - drill={$drill} wipeMethod={$this->wipeMethod}", $logFile);
	}
	
	protected function setDiskIdentifier($diskIdentifier) {
		/*
		 * This function will set the Disk Identifier that was handed into the 
		 * class
		 */
		writeToLogFile("Hard Drive Class ", "setDiskIdentifier - begin - diskIdentifier={$diskIdentifier}", $logFile);
		$this->diskIdentifier = $diskIdentifier;
		writeToLogFile("Hard Drive Class ", "setDiskIdentifier - end - diskIdentifier={$diskIdentifier}", $logFile);
	}
		
	protected function setSingleFdiskOutput() {
		/*
		 * Using a single disk identifier create the fdisk output
		 * for the creation of the data for this single hard drive
		 */
		writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - begin - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $logFile);
		
		if ($this->wipeMethod == 'gdisk') {
			// Create the hdparm command to be executed
			writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - inside begin true if - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $logFile);
			
			$command = "sudo fdisk {$this->diskIdentifier} -l | grep -e \"^/\"";
			
			// Execute the command passing the output to class properties
			exec($command, $this->singleFdiskOutput);
			writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - inside end true if - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $logFile);

		} elseif ($this->wipeMethod == 'drill') {

			writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - inside begin false if - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $logFile);
			
			// This was a drill so set the fdisk class property to null
			$this->singleFdiskOutput = null;
		
			writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - inside end false if - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $logFile);

		}
		writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - end - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $logFile);
		
	}
		
	protected function setSingleHdparmOutput() {
		/*
		 * This function will generate the Hard Drive parameter information
		 * for this hard drive instance
		 */
		writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - begin - singleHdparmOutput={$this->singleHdparmOutput} wipeMethod={$this->wipeMethod}", $logFile);
		
		if ($this->wipeMethod == 'gdisk') {
			// Create the hdparm command to be executed
			writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - inside begin true if - singleHdparmOutput={$this->singleHdparmOutput} wipeMethod={$this->wipeMethod}", $logFile);
			$command = "sudo hdparm -i {$this->diskIdentifier} | grep -e SerialNo=";
			
			// Execute the command passing the output and return to class properties
			exec($command, $this->singleHdparmOutput,$this->singleHdparmReturn);
			writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - inside end true if - singleHdparmOutput={$this->singleHdparmOutput} wipeMethod={$this->wipeMethod}", $logFile);
		} elseif ($this->wipeMethod == 'drill') {
			writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - inside begin false if - singleHdparmOutput={$this->singleHdparmOutput} wipeMethod={$this->wipeMethod}", $logFile);
			
			// This was a drill so set the hdparm class properties to null
			$this->singleHdparmOutput = null;
			$this->singleHdparmReturn = null;
			writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - inside end false if - singleHdparmOutput={$this->singleHdparmOutput} wipeMethod={$this->wipeMethod}", $logFile);
		}
		writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - end - singleHdparmOutput={$this->singleHdparmOutput} wipeMethod={$this->wipeMethod}", $logFile);
	}
	
	protected function setPartitionCount() {
		/*
		 * This function will use the fdisk output generated previously
		 * and determine how many partitions are active on the current 
		 * drive
		 */
		
		writeToLogFile("Hard Drive Class ", "setPartitionCount - begin - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
				
		if ($this->wipeMethod == 'gdisk') {		
			writeToLogFile("Hard Drive Class ", "setPartitionCount - begin if wipeMethod gdisk - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
			
			//Since we are grepping out only the lines that have partition information then any additions to the array
			//are partitions
			$this->partitionCount = count($this->singleFdiskOutput);

			writeToLogFile("Hard Drive Class ", "setPartitionCount - end if wipeMethod gdisk - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
			
		} elseif ($this->wipeMethod == 'drill') {
			
			//Since we drilled the hard drive there are no partitions available, so set it to 0
			writeToLogFile("Hard Drive Class ", "setPartitionCount - begin if wipeMethod drill - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
			
			$this->partitionCount = 0;

			writeToLogFile("Hard Drive Class ", "setPartitionCount - end if wipeMethod drill - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
		}
		writeToLogFile("Hard Drive Class ", "setPartitionCount - end - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
		
	}	

	protected function setValidDisk() {
		/*
		 * This function will utilize the hdparm command to determine
		 * if the drive in question is a valid disk or not. A non-valid
		 * disk (i.e., USB, CD) will pass back an error when the hdparm
		 * command is run on it. This will allow us to identify whether
		 * or not the drive is valid
		 */
		writeToLogFile("Hard Drive Class ", "setValidDisk - begin - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
		
		if ( $this->wipeMethod == 'gdisk' ) {
			
			// This is a normal gdisked hard drive so we can handle the valid disk through normal means
			writeToLogFile("Hard Drive Class ", "setValidDisk - begin wipemethod if gdisk - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
			
			if (empty($this->singleHdparmOutput)) {
				
				writeToLogFile("Hard Drive Class ", "setValidDisk - begin if empty singleHdparmOutput - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
				//No hdparm output detected so call the method to generate it
				$this->setSingleHdparmOutput();
				writeToLogFile("Hard Drive Class ", "setValidDisk - end if empty singleHdparmOutput - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
				
			}
			
			if ($this->singleHdparmReturn == 0) {
				/*
				 * HDPARM returned a value so this is a valid disk to be reviewed
				 */
				writeToLogFile("Hard Drive Class ", "setValidDisk - begin singleHdparmReturn if 0 - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
				
				$this->validDisk = true;

				writeToLogFile("Hard Drive Class ", "setValidDisk - end singleHdparmReturn if 0 - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);

			} else {
				/*
				 * HDPARM returned an error therefore it is most likely a USB / CD bootable disk
				 * so we will make this a non-valid disk for purpose of disk wipe
				 */
				writeToLogFile("Hard Drive Class ", "setValidDisk - begin singleHdparmReturn if not 0 - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
				$this->validDisk = false;
				writeToLogFile("Hard Drive Class ", "setValidDisk - end singleHdparmReturn if not 0 - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
				
			}
		} elseif ( $this->wipeMethod == 'drill' ) {

			writeToLogFile("Hard Drive Class ", "setValidDisk - begin wipemethod if drill - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
			
			// This drive was drilled and therefore not present to have this determination made so is automatically considered valid
			$this->validDisk = true;

			writeToLogFile("Hard Drive Class ", "setValidDisk - end wipemethod if drill - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
		}
		
		writeToLogFile("Hard Drive Class ", "setValidDisk - end - singleHdparmOutput={$this->singleHdparmOutput} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $logFile);
		
	}
	
	protected function setDiskSerialNumber() {
		/*
		 * This function will utilize the hdparm command to gather the
		 * serial information from the physical disk and return it.
		 */

		writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin - singleHdparmOutput={$this->singleHdparmOutput} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $logFile);

		if ($this->wipeMethod == 'gdisk') {

			writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin if wipeMethod gdisk - singleHdparmOutput={$this->singleHdparmOutput} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $logFile);
			
			// Set a temporary array to hold the exploded identification line
			$identification = explode(",",$this->singleHdparmOutput);

			// After the explode the Serial Number is stored in the 3rd array index
			$this->serialNumber = $this->_cleanHDIdentification($identification[2]);
			writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - end if wipeMethod gdisk - singleHdparmOutput={$this->singleHdparmOutput} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $logFile);
			
		} elseif ($this->wipeMethod == 'drill') {
			/*
			 * Drill flag was passed into the function so we will need to prompt for a serial number
			 */
			
			writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin if wipeMethod drill - singleHdparmOutput={$this->singleHdparmOutput} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $logFile);
			
			do {
				$driveSerial = $this->__getDrilledHardDriveSerialNumber();
			} while ($driveSerial != false);
			
			// Assign the confirmed serial number to the instance class property
			$this->serialNumber = $driveSerial;
			
			writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - end if wipeMethod drill - singleHdparmOutput={$this->singleHdparmOutput} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $logFile);
		}
		writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - end - singleHdparmOutput={$this->singleHdparmOutput} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $logFile);
	}
	
	protected function setWipeValidation() {
		/*
		 * This function will determine the disk wipe status based upon the partition count class property.
		 * A hard drive that has been wiped should have 0 partitions. Any partition count other than 0,
		 * will result in a negative disk wipe status
		 */
		
		writeToLogFile("Hard Drive Class ", "setWipeValidation - begin - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
		
		if ($this->wipeMethod == 'drill') {
			
			/*
			 * Drill flag was passed into the function so we will just mark the wipe as being validated
			 */
			writeToLogFile("Hard Drive Class ", "setWipeValidation - begin if wipeMethod drill - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
			
			$this->wipeValidation = true;
			
			writeToLogFile("Hard Drive Class ", "setWipeValidation - end if wipeMethod drill - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
			
		} elseif ($this->wipeMethod == 'gdisk') {

			/*
			 * Drill flag was not passed in so we will follow the normal procedures for 
			 * checking 
			 */
			// Check the partition count to determine if it is 0
			writeToLogFile("Hard Drive Class ", "setWipeValidation - begin if wipeMethod gdisk - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
			
			if ($this->partitionCount === 0) {
				writeToLogFile("Hard Drive Class ", "setWipeValidation - begin if partitionCount 0 - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
				
				// There are no partitions so this is a successful wipe
				$this->wipeValidation = true;
				
				writeToLogFile("Hard Drive Class ", "setWipeValidation - end if partitionCount 0 - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
			}
			writeToLogFile("Hard Drive Class ", "setWipeValidation - end if wipeMethod gdisk - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
			
		}
		writeToLogFile("Hard Drive Class ", "setWipeValidation - end - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $logFile);
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
	
	private function _getDrilledHardDriveSerialNumber() {
		/*
		 * This function will read stdin to get the serial number of the hard drive that was drilled
		 */		
		
		//Get the serial number from the user
		echo "What is the serial number of the drilled hard drive? \n";
		$inputSerial = fgets(STDIN);

		do {
			//Loop until the user enters a y or a n to determine if the hard drive was drilled
			echo "The serial number of the hard drive you drilled is {$inputSerial}. Is this correct? [Y / N] \n";
			do {
				//Get a single character from the STDIN
				$answer = fgetc(STDIN);
			} while ( trim($answer) == '');
		} while (upper($answer) != 'Y' && upper($answer) !='N');
		
		if (upper($answer) == 'Y') {
			// The user confirmed the hard drive serial so return it
			return $inputSerial;
		} elseif (upper($answer) == 'N') {
			// The user did not confirm the hard drive serial so start over
			return false;
		}
	}
}

/*
 * End of File: HardDrive.Class.php
 * Class: HardDrive
 * File: ./includes/HardDrive.Class.php
 */