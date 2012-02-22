<?php

require_once('HardDrive.Class.php');

class WorkstationHardDrive extends HardDrive {
	
	public function __construct($diskIdentifier, $drill = false) {
		/*
		 * This will be the construct process for the hard drive class,
		 * and we will need to get all of the items setup
		 */

		// Instantiate the parent constructor
		parent::__construct();
		
		// Set the Hdparm output for this hard drive instance - specifically for gathering the serial number
		$this->setSingleHdparmOutput();
		
		// Determine whether or not this is a valid disk
		$this->setValidDisk();
		
		// Set the hard drive serial number
		$this->setDiskSerialNumber();
	}
	
	protected function setSingleHdparmOutput() {
		/*
		 * This function will generate the Hard Drive parameter information
		 * for this hard drive instance
		 */
		writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - begin - singleHdparmOutput={isset($this->singleHdparmOutput[0]) ? $this->singleHdparmOutput[0] : null} wipeMethod={$this->wipeMethod}", $this->logFile);
		
		if ($this->wipeMethod == 'gdisk') {
			// Create the hdparm command to be executed
			writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - inside begin true if - singleHdparmOutput={isset($this->singleHdparmOutput[0]) ? $this->singleHdparmOutput[0] : null} wipeMethod={$this->wipeMethod}", $this->logFile);
			$command = "sudo hdparm -i {$this->diskIdentifier} | grep -e SerialNo=";
			
			// Execute the command passing the output and return to class properties
			exec($command, $this->singleHdparmOutput,$this->singleHdparmReturn);
			writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - inside end true if - singleHdparmOutput={$this->singleHdparmOutput[0]} wipeMethod={$this->wipeMethod}", $this->logFile);
		} elseif ($this->wipeMethod == 'drill') {
			writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - inside begin false if - singleHdparmOutput={$this->singleHdparmOutput[0]} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			// This was a drill so set the hdparm class properties to null
			$this->singleHdparmOutput = null;
			$this->singleHdparmReturn = null;
			writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - inside end false if - singleHdparmOutput={$this->singleHdparmOutput[0]} wipeMethod={$this->wipeMethod}", $this->logFile);
		}
		writeToLogFile("Hard Drive Class ", "setSingleHdparmOutput - end - singleHdparmOutput={$this->singleHdparmOutput[0]} wipeMethod={$this->wipeMethod}", $this->logFile);
	}
	
	protected function setValidDisk() {
		/*
		 * This function will utilize the hdparm command to determine
		 * if the drive in question is a valid disk or not. A non-valid
		 * disk (i.e., USB, CD) will pass back an error when the hdparm
		 * command is run on it. This will allow us to identify whether
		 * or not the drive is valid
		 */
		writeToLogFile("Hard Drive Class ", "setValidDisk - begin - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
		
		if ( $this->wipeMethod == 'gdisk' ) {
			
			// This is a normal gdisked hard drive so we can handle the valid disk through normal means
			writeToLogFile("Hard Drive Class ", "setValidDisk - begin wipemethod if gdisk - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			if (empty($this->singleHdparmOutput)) {
				
				writeToLogFile("Hard Drive Class ", "setValidDisk - begin if empty singleHdparmOutput - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
				//No hdparm output detected so call the method to generate it
				$this->setSingleHdparmOutput();
				writeToLogFile("Hard Drive Class ", "setValidDisk - end if empty singleHdparmOutput - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
				
			}
			
			if ($this->singleHdparmReturn == 0) {
				/*
				 * HDPARM returned a value so this is a valid disk to be reviewed
				 */
				writeToLogFile("Hard Drive Class ", "setValidDisk - begin singleHdparmReturn if 0 - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
				
				$this->validDisk = true;

				writeToLogFile("Hard Drive Class ", "setValidDisk - end singleHdparmReturn if 0 - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);

			} else {
				/*
				 * HDPARM returned an error therefore it is most likely a USB / CD bootable disk
				 * so we will make this a non-valid disk for purpose of disk wipe
				 */
				writeToLogFile("Hard Drive Class ", "setValidDisk - begin singleHdparmReturn if not 0 - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
				$this->validDisk = false;
				writeToLogFile("Hard Drive Class ", "setValidDisk - end singleHdparmReturn if not 0 - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
				
			}
		} elseif ( $this->wipeMethod == 'drill' ) {

			writeToLogFile("Hard Drive Class ", "setValidDisk - begin wipemethod if drill - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
			
			// This drive was drilled and therefore not present to have this determination made so is automatically considered valid
			$this->validDisk = true;

			writeToLogFile("Hard Drive Class ", "setValidDisk - end wipemethod if drill - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
		}
		
		writeToLogFile("Hard Drive Class ", "setValidDisk - end - singleHdparmOutput={$this->singleHdparmOutput[0]} validDisk={$this->validDisk} wipeMethod={$this->wipeMethod}", $this->logFile);
		
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
	
}

/*
 * End of File: HardDrive.Class.php
 * Class: HardDrive
 * File: ./includes/HardDrive.Class.php
 */