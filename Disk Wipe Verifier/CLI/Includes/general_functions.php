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
		if ($display_array['FullDiskWipeVerify'] == 'FAILED') {
			//Overall verification failed so display the failure in asterisks
			displayAsteriskMessage("FAIL FAIL");
			//Write to the log file
			writeToLogFile("Overall Verification Failed", "1 or more disks had active partitions", $logFile);
			
			echo "\n";
			
			//Let's loop through the array and start displaying values
			foreach ($display_array as $key => $value) {
				
				if (stripos($key, "DiskNo_") !== FALSE) {
					//Start building the display line with the disk code
					$displayLine = "Disk Code: {$key} \n";
					//Let's loop through the Disk Sub-Array
					foreach ($value as $subKey => $subValue) {
						//Add each disk subkey to the current line and display the value
						$displayLine .= "     {$subKey} = {$subValue} \n";
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
		} elseif ($display_array['FullDiskWipeVerify'] == 'PASSED') {
			//Write to the log file
			writeToLogFile("Overall Verification Passed", "No disks had active partitions", $logFile);
			//Let's loop through the array and start displaying values
			foreach ($display_array as $key => $value) {
				if (stripos($key, "DiskNo_") !== FALSE) {
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
		global $validation_array;
		global $disk_array;
		global $partition_array;
		global $size_array;
		global $num_of_disks;
		
		//Set the default disk wipe to be true - only change if it actually fails
		$diskWipeVerified = 'PASSED';
		
		//This is the array that will hold just the display information
		$display_array = array();
		
		//Set the Sort Code
		$display_array['sort_code'] = $validation_array['sort_code'];
		
		//Set the machine serial number
		$display_array['machine_serial'] = $validation_array['machine_serial'];
		
		if ($num_of_disks > 0) {
			/*
			 * There are disks available in the machine so let's parse to see 
			 * which ones are physical disks
			 */
			for ($i = 1; $i <= $num_of_disks; $i++) {
				//Count through each disk to determine
				//Does this disk contain the string for a failed HDParm meaning it is our USB Drive

				if ((stripos($validation_array['disks'][$i]['disk_model'],'Unknown (USB?)')) === FALSE) {
					//This is a valid disk so we will pass back the information
					$diskID = 'DiskNo_' . $i;
					//Hand the serial number from the original validation array
					$display_array[$diskID]['SerialNumber'] = $validation_array['disks'][$i]['disk_serial'];
					//Hand the disk size from the original validation array
					$display_array[$diskID]['disk_size'] = $validation_array['disks'][$i]['disk_size'];
					//Hand the partition count from the original validation array
					$display_array[$diskID]['PartitionCount'] = $validation_array['disks'][$i]['disk_part_count'];
					//Validate if there are any partitions on the disk - then G-Disk failed on this disk
					$display_array[$diskID]['DiskWipeVerify'] = ($display_array[$diskID]['PartitionCount'] > 0) ? 'FAILED' : 'PASSED';
					//If the Gdisk failed on this disk, then update overall verification to FALSE
					if ($display_array[$diskID]['DiskWipeVerify'] === "FAILED") {
						$diskWipeVerified = 'FAILED';
					}
				}
			}
			
			$display_array['FullDiskWipeVerify'] = ($diskWipeVerified == 'FAILED') ? 'FAILED' : 'PASSED';
			
		} else {
			
			//No disks were found in the machine so we are going to return false so an error can be handled
			return FALSE;
			
		}
		
		//Return the array back to the calling function
		return $display_array;
	}
	
/*
 * End of File: general_functions.php
 * Class: None
 * File: ./includes/general_functions.php
 */