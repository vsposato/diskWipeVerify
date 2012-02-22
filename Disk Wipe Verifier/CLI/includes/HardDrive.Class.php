<?php

class HardDrive {
	
	protected $diskIdentifier = ''; //Completed
	protected $serialNumber = ''; //Completed
	protected $partitionCount = 0; //Completed
	protected $validDisk = false; //Completed
	protected $wipeMethod = 'gdisk';
	protected $wipeValidation = false; //Completed
	protected $singleFdiskOutput = array(); //Completed
	protected $singleHdparmOutput = array(); //Completed
	protected $singleHdparmReturn = ''; //Completed
	protected $logFile = '';
	
	public function __construct($diskIdentifier, $drill = false) {
		/*
		 * This will be the construct process for the hard drive class,
		 * and we will need to get all of the items setup
		 */

		/* 
		 * Define global variables required to perform functions
		 */
		global $logFile;
		
		$this->logFile = $logFile;
		
		WriteToLogFile("Hard Drive Class ", "Instantiate", $this->logFile);
		
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
		
		// Set the partition count for this hard drive
		$this->setPartitionCount();
		
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
		writeToLogFile("Hard Drive Class ", "setWipeMethod - begin - drill={$drill} wipeMethod={$this->wipeMethod}", $this->logFile);
		
		if ($drill) {

			writeToLogFile("Hard Drive Class ", "setWipeMethod - inside begin true drill if - drill={$drill} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			// The drill flag is on so mark the hard drive as drilled
			$this->wipeMethod = 'drill';

			writeToLogFile("Hard Drive Class ", "setWipeMethod - inside end true drill if - drill={$drill} wipeMethod={$this->wipeMethod}", $this->logFile);
		} else {
			
			writeToLogFile("Hard Drive Class ", "setWipeMethod - inside begin false drill if - drill={$drill} wipeMethod={$this->wipeMethod}", $this->logFile);

			// The drill flag is off so mark the hard drive as gdisked (the default)
			$this->wipeMethod = 'gdisk';

			writeToLogFile("Hard Drive Class ", "setWipeMethod - inside end false drill if - drill={$drill} wipeMethod={$this->wipeMethod}", $this->logFile);
		}
		writeToLogFile("Hard Drive Class ", "setWipeMethod - end - drill={$drill} wipeMethod={$this->wipeMethod}", $this->logFile);
	}
	
	protected function setDiskIdentifier($diskIdentifier) {
		/*
		 * This function will set the Disk Identifier that was handed into the 
		 * class
		 */
		writeToLogFile("Hard Drive Class ", "setDiskIdentifier - begin - diskIdentifier={$diskIdentifier}", $this->logFile);
		$this->diskIdentifier = $diskIdentifier;
		writeToLogFile("Hard Drive Class ", "setDiskIdentifier - end - diskIdentifier={$diskIdentifier}", $this->logFile);
	}
		
	protected function setSingleFdiskOutput() {
		/*
		 * Using a single disk identifier create the fdisk output
		 * for the creation of the data for this single hard drive
		 */
		writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - begin - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $this->logFile);
		
		if ($this->wipeMethod == 'gdisk') {
			// Create the hdparm command to be executed
			writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - inside begin true if - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			$command = "sudo fdisk {$this->diskIdentifier} -l | grep -e \"^/\"";
			
			// Execute the command passing the output to class properties
			exec($command, $this->singleFdiskOutput);
			writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - inside end true if - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $this->logFile);

		} elseif ($this->wipeMethod == 'drill') {

			writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - inside begin false if - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			// This was a drill so set the fdisk class property to null
			$this->singleFdiskOutput = null;
		
			writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - inside end false if - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $this->logFile);

		}
		writeToLogFile("Hard Drive Class ", "setSingleFdiskOutput - end - singleFdiskOutput={$this->singleFdiskOutput} wipeMethod={$this->wipeMethod}", $this->logFile);
		
	}
		
	protected function setPartitionCount() {
		/*
		 * This function will use the fdisk output generated previously
		 * and determine how many partitions are active on the current 
		 * drive
		 */
		
		writeToLogFile("Hard Drive Class ", "setPartitionCount - begin - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
				
		if ($this->wipeMethod == 'gdisk') {		
			writeToLogFile("Hard Drive Class ", "setPartitionCount - begin if wipeMethod gdisk - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			//Since we are grepping out only the lines that have partition information then any additions to the array
			//are partitions
			$this->partitionCount = count($this->singleFdiskOutput);

			writeToLogFile("Hard Drive Class ", "setPartitionCount - end if wipeMethod gdisk - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
			
		} elseif ($this->wipeMethod == 'drill') {
			
			//Since we drilled the hard drive there are no partitions available, so set it to 0
			writeToLogFile("Hard Drive Class ", "setPartitionCount - begin if wipeMethod drill - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			$this->partitionCount = 0;

			writeToLogFile("Hard Drive Class ", "setPartitionCount - end if wipeMethod drill - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
		}
		writeToLogFile("Hard Drive Class ", "setPartitionCount - end - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
		
	}	

	protected function setDiskSerialNumber() {
		/*
		 * This function will utilize the hdparm command to gather the
		 * serial information from the physical disk and return it.
		 */

		writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin - singleHdparmOutput={$this->singleHdparmOutput[0]}  validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);

		if ($this->wipeMethod == 'gdisk') {

			writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin if wipeMethod gdisk - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			if ($this->validDisk) {
				// This is a valid disk, so we can get the serial number through the normal process
				writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin if validDisk true - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);
				
				// Set a temporary array to hold the exploded identification line
				$identification = explode(",", $this->singleHdparmOutput[0]);
	
				// After the explode the Serial Number is stored in the 3rd array index
				$this->serialNumber = $this->_cleanHDIdentification($identification[2]);

				writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - end if validDisk true - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);
			} else {
				// This is not a valid disk, and therefore there is no way to get a serial number from this disk
				writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin if validDisk false - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);
				
				// Set value to 10 zero's 
				$this->serialNumber = '0000000000';
				
				writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin if validDisk false - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);
				
			}
			writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - end if wipeMethod gdisk - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);
			
		} elseif ($this->wipeMethod == 'drill') {
			/*
			 * Drill flag was passed into the function so we will need to prompt for a serial number
			 */
			
			writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - begin if wipeMethod drill - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			do {
				$driveSerial = $this->_getDrilledHardDriveSerialNumber();
			} while ($driveSerial === false);
			
			// Assign the confirmed serial number to the instance class property
			$this->serialNumber = $driveSerial;
			
			writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - end if wipeMethod drill - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} serialNumber={$this->serialNumber} wipeMethod={$this->wipeMethod}", $this->logFile);
		}
		writeToLogFile("Hard Drive Class ", "setDiskSerialNumber - end - singleHdparmOutput={$this->singleHdparmOutput[0]} serialNumber={$this->serialNumber} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
	}
	
	protected function setWipeValidation() {
		/*
		 * This function will determine the disk wipe status based upon the partition count class property.
		 * A hard drive that has been wiped should have 0 partitions. Any partition count other than 0,
		 * will result in a negative disk wipe status
		 */
		
		writeToLogFile("Hard Drive Class ", "setWipeValidation - begin - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
		
		if ($this->wipeMethod == 'drill') {
			
			/*
			 * Drill flag was passed into the function so we will just mark the wipe as being validated
			 */
			writeToLogFile("Hard Drive Class ", "setWipeValidation - begin if wipeMethod drill - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			$this->wipeValidation = true;
			
			writeToLogFile("Hard Drive Class ", "setWipeValidation - end if wipeMethod drill - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
			
		} elseif ($this->wipeMethod == 'gdisk') {

			/*
			 * Drill flag was not passed in so we will follow the normal procedures for 
			 * checking 
			 */
			// Check the partition count to determine if it is 0
			writeToLogFile("Hard Drive Class ", "setWipeValidation - begin if wipeMethod gdisk - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			if ($this->partitionCount === 0) {
				writeToLogFile("Hard Drive Class ", "setWipeValidation - begin if partitionCount 0 - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
				
				// There are no partitions so this is a successful wipe
				$this->wipeValidation = true;
				
				writeToLogFile("Hard Drive Class ", "setWipeValidation - end if partitionCount 0 - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
			}
			writeToLogFile("Hard Drive Class ", "setWipeValidation - end if wipeMethod gdisk - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
			
		}
		writeToLogFile("Hard Drive Class ", "setWipeValidation - end - partitionCount={$this->partitionCount} wipeMethod={$this->wipeMethod}", $this->logFile);
	}
	
	protected function _cleanHDIdentification($HDIdentification) {
		//This function will take the model, firmware and serial and return them without
		//the beginning entry
		
		//The standard is an = sign so break out from there
		$temp_hd_info = explode('=',$HDIdentification);
		
		//Return the 2nd index of the array returned which will hold the important information
		return $temp_hd_info[1];
	}
	
	protected function _getDrilledHardDriveSerialNumber() {
		/*
		 * This function will read stdin to get the serial number of the hard drive that was drilled
		 */		

		//Get the serial number from the user
		echo "What is the serial number of the drilled hard drive? \n";
		$inputSerial = readline("");

		$answer = getResponseFromUser("The serial number of the hard drive you drilled is {$inputSerial}. Is this correct? [Y / N] \n", array('y','n'), FALSE);
		
		if (strtoupper($answer) == 'Y') {
			// The user confirmed the hard drive serial so return it
			echo "Accepted \n";
			return $inputSerial;
		} elseif (strtoupper($answer) == 'N') {
			// The user did not confirm the hard drive serial so start over
			echo "Rejected \n";
			return false;
		}		
	}
}

/*
 * End of File: HardDrive.Class.php
 * Class: HardDrive
 * File: ./includes/HardDrive.Class.php
 */