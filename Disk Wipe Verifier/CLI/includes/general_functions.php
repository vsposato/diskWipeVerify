<?php

	function multi_array_key_exists($needle, $haystack) {
		foreach ($haystack as $key=>$value) {
			if ($needle===$key) {
				return $key;
			}
			if (is_array($value)) {
				if(multi_array_key_exists($needle, $value)) {
					return $key . ":" . multi_array_key_exists($needle, $value);
				}
			}
		}
		return false;
	}
	
	function objectToArray($object) { 
		  if(!is_object( $object ) && !is_array( $object )) { 
		      return $object; 
		  } 
		  if(is_object($object) ) { 
		      $object = get_object_vars( $object ); 
		  }
		  return array_map('objectToArray', $object ); 
	}  

	function buildXMLRPCMessage($validation_array) {
		/*
		 * We are going to take the validation array that has been submitted and 
		 * break it down into a valid XML RPC message to be sent to the web server
		 * that will then code it and add it to the database
		 */
		
		$parameters = array(XML_RPC_encode($validation_array));
		$message = new XML_RPC_Message('submitDiskWipe', $parameters);
		
		return $message;
	}

	function displayNormalMessage($display_array) {
		/*
		 * This function will take an array input and display it on the screen using normal characters
		 * and will not use the asterisk message except for overall failure
		 */
		global $logFile;
		
		//Check to determine whether or not overall verification failed
		if ($display_array['wipe_status'] == 'FAILED') {
			//Overall verification failed so display the failure in asterisks
			displayAsteriskMessage("FAIL FAIL");
			//Write to the log file
			writeToLogFile("Overall Verification Failed", "1 or more disks had active partitions", $logFile);
			
			echo "\n";
			
			//Let's loop through the array and start displaying values
			foreach ($display_array as $key => $value) {
				
				if (stripos($key, "disks") !== FALSE) {
					//Let's loop through the Disk Sub-Array

					foreach ($value as $subKey => $subValue) {
						
						// We are now in side the disks sub-array so let's get the first key
						
						foreach ($value as $subKey2 => $subValue2) {
							//Start building the display line with the disk code
							$displayLine = "Disk Code: {$key} \n";
							//Add each disk subkey to the current line and display the value
							$displayLine .= "     {$subKey} = {$subValue} \n";
						}
						
					}
					//Display the line on the screen
					echo $displayLine;
					
					//Continue the foreach loop
					continue;
					
				} else {
					
					//This isn't a disk so just display the key value pair
					$displayLine = "{$key} = {$value} \n";
					
					//Display the line on the screen
					echo $displayLine;
					
					//Continue the foreach loop
					continue;
				}
			}
		} elseif ($display_array['wipe_status'] == 'PASSED') {
			//Write to the log file
			writeToLogFile("Overall Verification Passed", "No disks had active partitions", $logFile);
			//Let's loop through the array and start displaying values
			foreach ($display_array as $key => $value) {
				if (stripos($key, "disks") !== FALSE) {
					//Start building the display line with the disk code
					$displayLine = "Disk Code: {$key} \n";
					//Let's loop through the Disk Sub-Array
					foreach ($value as $subKey => $subValue) {
						//Add each disk subkey to the current line and display the value
						$displayLine .= "     {$subKey} = {$subValue}\n";
					}
					//Display the line on the screen
					echo $displayLine;
					//Continue the foreach loop
					continue;
				} else {
					//This isn't a disk so just display the key value pair
					$displayLine = "{$key} = {$value} \n";
					//Display the line on the screen
					echo $displayLine;
					//Continue the foreach loop
					continue;
				}
			}
			displayAsteriskMessage($display_array['machine_serial']);
		}
	}

	function verifyDiskWipe() {
		/*
		 * This function will take the information gathered and determine whether or
		 * not the disk wipe validation passed or failed
		 */
		global $checkWorkstation;
		global $sortCode;
		
		//This is the array that will hold just the display information
		$display_array = array();
		
		//Set the Sort Code
		$display_array['sort_code'] = $sortCode;
		
		//Set the machine serial number
		$display_array['machine_serial'] = $checkWorkstation->getSerialNumber();
		
		// Set the machine wipe status
		$display_array['wipe_status'] = $checkWorkstation->getDiskWipeStatus();
		
		// Set the disk wipe method
		$display_array['wipe_method'] = $checkWorkstation->getDiskWipeMethod();
		
		// Set the number of hard drives
		$display_array['disk_count'] = $checkWorkstation->getValidDriveCount();
		
		//Gather the hard drive information from the machine class
		$display_array['disks'] = $checkWorkstation->getHardDrives();
				
		//Return the array back to the calling function
		return $display_array;
	}

	function setSortCode() {
		//Present the user with a prompt to get the sort code of the site we are at
		global $sortCode;
		
		echo "What sort code are you at? (enter below): \n";
		do{
			$sortCode = trim(fgets(STDIN));
		}while (!preg_match('/^\d{7}$/',$sortCode));
		
		//Lets confirm that the user really meant that sort code
		echo "{$sortCode} - are you sure? (yes / no) \n";
		
		//Here we are going to run an input loop to confirm that the user really meant
		//this sort code
		do {
			//Read 4 characters from the keyboard
			$answer = fgets(STDIN);
			
			//Trim the response and convert it to upper
			$answer = trim(strtoupper($answer));
			
			//Check to see if the user said either yes or no
			if (($answer != "YES") AND ($answer != "NO")) {
				// You must enter YES or NO, nothing else
				// The user said something other than yes or no so loop
				echo "Are you at sort code {$sortCode}? \n";
				echo "You must enter either yes or no! \n";
			}
		} while (($answer != "YES")  AND ($answer != "NO"));

		//The loop exited because the user said yes or no, 
		//if he said no then take him back into the function
		if ($answer == "NO") {
			setSortCode;
		} 
	}
	
/*
 * End of File: general_functions.php
 * Class: None
 * File: ./includes/general_functions.php
 */