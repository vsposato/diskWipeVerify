!/usr/bin/php -q
<?php
	/* 
	 * Define some global variables here
	 * 
	 * $sortCode - this will hold the sort code of the site this machine was located at
	 * $vaidationArray - this will be the array that will eventually be sent to the server
	 * 
	 */

	global $sortCode;
	global $validationArray;
	//Set up the data array for the final data
	$validation_array = array();
	
	/* Define STDIN in case it wasn't defined somewhere else */
	if (! defined("STDIN")) {
		define("STDIN", fopen('php://stdin','r'));
	}
	
	function getSystemSerialNumber() {
		/*
		 * Here we will use the exec command to pull the system serial number and then
		 * hand it back to the calling function
		 */
		
		 exec('dmidecode -s system-serial-number', $tempSerialNumber);
		 
		 //Shift the first array item off to the return value
		 return array_shift($tempSerialNumber);
	}	
	
	function getBaseboardSerialNumber() {
		/*
		 * Here we will use the exec command to pull the baseboard serial number and then
		 * hand it back to the calling function
		 */
		
		 exec('dmidecode -s baseboard-serial-number', $tempSerialNumber);
		 
		 //Shift the first array item off to the return value
		 return array_shift($tempSerialNumber);
		
	}
	
	function getChassisSerialNumber() {
		/*
		 * Here we will use the exec command to pull the chassis serial number and then
		 * hand it back to the calling function
		 */
		
		 exec('dmidecode -s chassis-serial-number', $tempSerialNumber);
		 
		 //Shift the first array item off to the return value
		 return array_shift($tempSerialNumber);
	}
	
	function cleanHDSize($HDSize) {
		//This function will remove the , from the sizing information returned
		//from the fdisk function
		
		//The standard separator for the hard drive size is a comma
		$temp_hd_size = explode(',', $HDSize);
		
		//Return the 1st array entry as this will house the friendly size
		return $temp_hd_size[0];
	}
	
	function cleanHDIdentification($HDIdentification) {
		//This function will take the model, firmware and serial and return them without
		//the beginning entry
		
		//The standard is an = sign so break out from there
		$temp_hd_info = explode('=',$HDIdentification);
		
		//Return the 2nd index of the array returned which will hold the important information
		return $temp_hd_info[1];
	}
	
	function getPartitionCountForDisk($partition_array, $disk_id) {
		/*
		 * This function will take an array of partitions and parse them to see
		 * how many partitions belong to this disk
		 * Returns - integer count of partitions
		 */
		$part_count = 0;
		foreach ($partition_array as $partition) {
			
			$partition_entry = stripos($partition, $disk_id);
			
			if ($partition_entry !== FALSE) {
				//We found the disk id inside the partition listing increment the partition count
				$part_count++;
			}
		}
		
		return $part_count;
	}
	
	function getSortCode() {		
		//Present the user with a prompt to get the sort code of the site we are at
		echo "What sort code are you at? (enter below): \n";
		$sortCode = fread(STDIN,7);
		
		//Lets confirm that the user really meant that sort code
		echo "{$sortCode} - are you sure? (yes / no) \n";
		
		//Here we are going to run an input loop to confirm that the user really meant
		//this sort code
		do {
			//Read 4 characters from the keyboard
			$answer = fread(STDIN, 4);
			
			//Trim the response and convert it to upper
			$answer = trim(strtoupper($answer));
			
			//Check to see if the user said either yes or no
			if (($answer != "YES") AND ($answer != "NO")) {
				// You must enter YES or NO, nothing else
				// The user said something other than yes or no so loop
				echo "You must enter either yes or no! \n";
				echo "What sort code are you at? (enter below) \n";
			}
		} while (($answer != "YES")  AND ($answer != "NO"));

		//The loop exited because the user said yes or no, 
		//if he said no then take him back into the function
		if ($answer == "NO") {
			getSortCode();
		} 
	}
	
	function determineValidSerialNumber($chassisSN, $baseboardSN, $systemSN) {
		/*
		 * Here we are going to validate that at least 2 of the 3 serial numbers match
		 * and assign that to the validation array
		 */
		if (($chassisSN == $baseboardSN) || ($chassisSN == $systemSN)) {
			//Chassis serial number matches one of the other 2 serial numbers - let's use that
			$validation_array['machine_serial'] = $chassisSN;
			$validation_array['serial_type'] = 'chassis';
			
		} else {
			//Chassis serial number did not match either of the other 2 so test to see if system serial does
			if (($systemSN == $baseboardSN)) {
				//System serial number matches baseboard so we will use that serial number
				$validation_array['machine_serial'] = $systemSN;
				$validation_array['serial_type'] = 'system';
			} else {
				//System serial number didn't match either but it is the most reliable so use it
				$validation_array['machine_serial'] = $systemSN;
				$validation_array['serial_type'] = 'default';
			}
		}		
	}
	
	function getFdiskInformation() {
		/*
		 * We are going to use the fdisk command to allow us to pull all the hard drive
		 * information from the sytem for later cleanup
		 */
		
		exec('fdisk -l', $temp_output);
		
		return $temp_output;
	}
	
	//This is the first run of the get sort code routine just to start the process
	getSortCode();
	
	//Show the user that we have started the data gathering process
	echo "Parsing data for sort code - {$sortCode} \n";
	
	//Set the sort code into the validation array
	$validation_array['sort_code'] = $sortCode;
	
	/* 
	 * Get the information from the DMIDECODE FUNCTION 
	 * that will allow us to get asset serial number
	 */
	$chassis_serial_number = getChassisSerialNumber();
	$baseboard_serial_number = getBaseboardSerialNumber();
	$system_serial_number = getSystemSerialNumber();
	
	/*
	 * Pass serial numbers to function to determine if any two of them match
	 * if they do, then we will use that one as the actual serial number. If
	 * not we will use the system serial number
	 */
	determineValidSerialNumber($chassis_serial_number, $baseboard_serial_number, $system_serial_number);
	
	/*
	 * Now we need to utilize the fdisk -l command to show me the
	 * partitions that are available to this system
	 */
	$fdisk_output = getFdiskInformation();
	
	/*
	 * Here we are going to be running a loop through the array created
	 * by the fdisk output to see how many drives we found
	 */
	$disk_array = Array();
	$partition_array = Array();
	$size_array = Array();
	
	foreach ($fdisk_output as $data) {
		/*
		 * Let's work through each individual line looking for disk
		 * entries or the partition runs
		 */
		$disk_entry = stripos($data, "Disk /");
		echo "{$disk_entry} \n";
		if ($disk_entry === FALSE) {
			//We did not find any disk entry so we are going to look for a partition entry
			$part_entry = stripos($data, "/dev/sd");
			
			if ($part_entry !== FALSE) {
				//We did find a partition entry so we are going to add it to the partition array
				$partition_array[] = substr($data, $part_entry, 9);
			}
		} elseif ($disk_entry !== FALSE) {
			//We found the Disk string inside of the data line so we are going to break it down
			//Build temporary string to get the 
			//$temp_disk = substr($data, $disk_entry, 13);
			$temp_disk = explode(':', $data);
			$disk_array[] = substr($temp_disk[0], 5, 8);
			$size_array[] = cleanHDSize($temp_disk[1]);
		}
		//We found nothing so we can just loop back
		echo "{$data} \n";
	}
	
	//See how many disks were found
	$num_of_disks = count($disk_array);
	$validation_array['total_disk_count'] = $num_of_disks;
	if ($num_of_disks > 0) {
		//Found some available disks so let's tell us about them
		echo "There are {$num_of_disks} disk(s) in this system \n";
		print_r($disk_array);
		echo "\n";
	}
	
	//See how many partitions were found
	$num_of_parts = count($partition_array);
	$validation_array['total_partition_count'] = $num_of_parts;
	if ($num_of_parts > 0) {
		//Found some partitions so let's tell us about them
		echo "There are {$num_of_parts} partition(s) in this system \n";
		print_r($partition_array);
		echo "\n";
	}
	
	echo "\n";
	print_r($size_array);
	echo "\n";
	/*
	 * Now we need to utilize the hdparm -i command to show all drive
	 * serial numbers to allow for all drives to have been output. 
	 * We will utilize the array we built to run hdparm on up to 3 drives
	 * any more than 3 drives and they will be ignored
	 */
	$disk_pass = 1;
	if (array_count_values($disk_array) > 0) {
		foreach($disk_array as $key=>$disk) {
			//Build the actual shell command
			$exec_command = "hdparm -i {$disk}";
			//Create the disk id variable
			$disk_id = substr($disk,(strlen($disk) - 3),3);
			//Create the dynamic variable for the hdparm output
			$hdparm_output = $disk_id . "_hdparm_output";
			//Create the dynamic variable for the hdparm return
			$hdparm_return = $disk_id . "_return";
			//Display the command prior to running
			echo "Executing - {$exec_command} {$hdparm_output} {$hdparm_return} \n";
			//Execute the command
			exec($exec_command, $$hdparm_output, $$hdparm_return);
			
			/*
			 * Here we are going to process the actual HDPARM output data to be able to determine
			 * harddrive model and serial number - soon to be function processHdparmOutput
			 */
			if ($$hdparm_return == 0) {
				//Return value of 0 means a successful run - so process the output
				foreach($$hdparm_output as $data) {
					$identification_entry = strpos($data, 'Model=');

					if ($identification_entry !== FALSE) {
						//This is the line we need
						//Separate the model, firmware, and serial number into an array
						$identification = explode(',', $data);
						$model_id = 'disk' . $disk_pass . '_model';
						$firmware_id = 'disk' . $disk_pass . '_fw';
						$serial_id = 'disk' . $disk_pass . '_serial';
						$count_id = 'disk' . $disk_pass . '_part_count';
						$size_id = 'disk' . $disk_pass . '_size';
						
						$validation_array[$model_id] = cleanHDIdentification($identification[0]);
						$validation_array[$firmware_id] = cleanHDIdentification($identification[1]);
						$validation_array[$serial_id] = cleanHDIdentification($identification[2]);
						$validation_array[$count_id] = getPartitionCountForDisk($partition_array, $disk_id);
						$validation_array[$size_id] = $size_array[$key];
					}
				} 
			} else {
				//A failed return value means the hard drive is most likely our USB drive
				//We need to create the values and put unknowns in there
					$model_id = 'disk' . $disk_pass . '_model';
					$firmware_id = 'disk' . $disk_pass . '_fw';
					$serial_id = 'disk' . $disk_pass . '_serial';
					$count_id = 'disk' . $disk_pass . '_part_count';
					$size_id = 'disk' . $disk_pass . '_size';
					$validation_array[$model_id] = 'Unknown (USB?)';
					$validation_array[$firmware_id] = 'Unknown (USB?)';
					$validation_array[$serial_id] = 'Unknown (USB?)';
					$validation_array[$count_id] = getPartitionCountForDisk($partition_array, $disk_id);
					$validation_array[$size_id] = $size_array[$key];										
			}
			//Increment the disk counter
			$disk_pass++;
		}
	}

	echo "\n";
	print_r($validation_array);	
	echo "\n";
	echo "FDISK OUTPUT \n";
	print_r($fdisk_output);
	echo "\n";
	echo "CHASSIS SERIAL=$chassis_serial_number \n";
	echo "BASEBOARD SERIAL=$baseboard_serial_number \n";
	echo "SYSTEM SERIAL=$system_serial_number \n";
	if (isset($sda_return)) {
		if ($sda_return == 0) {	
			echo "SDA HDPARM \n";
			print_r($sda_hdparm_output);
			echo "\n";
			echo "SDA RETURN \n";
			echo $sda_return;
			echo "\n";
		}
	}
	if (isset($sdb_return)) {
		if ($sdb_return == 0) {
			echo "SDB HDPARM \n";
			print_r($sdb_hdparm_output);
			echo "\n";
			echo "SDB RETURN \n";
			echo $sdb_return;
			echo "\n";
		}
	}
	if (isset($sdc_return)) {
		if ($sdc_return == 0) {
			echo "SDC HDPARM \n";
			print_r($sdc_hdparm_output);
			echo "\n";
			echo "SDC RETURN \n";
			echo $sdc_return;
			echo "\n";
		}
	}
/*
 * End of file: DiskWipeVerify.php
 * Location: /DiskWipeVerify.php
 */
?>