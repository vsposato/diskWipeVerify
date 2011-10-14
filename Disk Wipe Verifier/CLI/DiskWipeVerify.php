!/usr/bin/php -q
<?php
	/* 
	 * Define some global variables here
	 * 
	 * $sortCode - this will hold the sort code of the site this machine was located at
	 * $vaidationArray - this will be the array that will eventually be sent to the server
	 * 
	 */

	/*
	 * Implement the PEAR package for XML_RPC for communication with the backend server
	 */
	require_once('XML/RPC.php');
	
	//Set the timezone
	date_default_timezone_set("America/New_York");
	
	/* Define STDIN in case it wasn't defined somewhere else */
	if (! defined("STDIN")) {
		define("STDIN", fopen('php://stdin','r'));
	}
	
	/*
	 * Define the values that are needed for the XML client connection
	 */
	define("xml_path", "dwv_xmlserver");
	define("xml_server", "diskwipe.nettechconsultants.com");
	define("xml_port", 80);
	define("xml_proxy", NULL);
	define("xml_proxy_port", NULL);
	define("xml_proxy_user", NULL);
	define("xml_proxy_pass", NULL);
	
	
	global $sortCode;
	global $validationArray;
	global $asteriskArray;
	
	//Set up the data array for the final data
	$validation_array = array();

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
	
	function getLogFilePath($sortCode) {
		/*
		 * This funciton will determine if a sortCode directory already exists
		 * if it doesn't it will create it, if it does it will just return the directory
		 */
		global $sortCode;
		$oldWorkingDirectory = getcwd();
		
		//Determine if we are already in the sortCode directory
		if( stripos($oldWorkingDirectory, $sortCode) === FALSE) {
			//We didn't find the sort code in the current working directory
			//Attempt to change directory to the sortCode directory
			if(! chdir($sortCode)) {
				//It didn't work so the directory needs to be created - with wide open permissions
				mkdir($sortCode, 0777);
				//Now change directory to the sortCode directory
				chdir($sortCode);
			}
		}
		
		//Get the current working directory and append the / 
		$currentWorkingDirectory = getcwd() . '/';
		
		//Return the current working path
		return $currentWorkingDirectory;
	}
	
	function createLogFile() {
		/*
		 * This function will create a file to be used as the log for this process
		 * and will set the fileHandle to be global
		 */
		
		global $logFile; 
		global $sortCode;
		
		//Determine the path that we will be using
		$path = getLogFilePath($sortCode);
		
		//Create the name of the logfile based upon the path
		//Remember we will rename if from TempLogFile.txt to 
		//SerialNumber-YYYY-MM-DD-HH-MM-SS.txt later
		$logFileName = $path . 'TempLogFile.txt';
		
		//Create the logfile and assign the handle to $logFile
		$logFile = fopen($logFileName, 'w');
		
		If (! $logFile ) {
			return FALSE;
		} else {
			return TRUE;
		}
		
	}
	
	function writeToLogFile($sectionName, $sectionData, $logFile) {
		/*
		 * This function will write data to the log file
		 * it will take a section name to identify what section
		 * of the log file we are currently working, a string or array of data
		 * to actually write, and the log file handle to work with
		 */

		global $logFile;
		
		//Now determine if sectionData is an array or a string
		if( is_array($sectionData) ) {
			//This is an array so we will need to run a loop to get all the data 
			//out to the file

			//Write the section name to the logFile
			fwrite($logFile, $sectionName);

			//Insert carriage return
			fwrite($logFile, "\n");
			
			foreach($sectionData as $key => $dataLine) { 
				//Write the line key
				fwrite($logFile, "{$key} = ");
				if (is_array($dataLine)) {
					foreach($dataLine as $subKey => $subDataLine) {
						//Write the key and data line for the sub array
						fwrite($logFile, "  {$subKey} = ");
						fwrite($logFile, "{$subDataLine} \n");
					}
				} else {
					//Write a line from the array
					fwrite($logFile, "{$dataLine} \n");
				}
			}
			//We completed the loop so return true
			return TRUE;
		} elseif( is_object($sectionData)) {

			//This is an object so we need to convert it to an array
			//We will use a conversion function, and then hand it back into the writeToLogFile function
			writeToLogFile($sectionName, objectToArray($sectionData), $logFile);

		} else {

			//Write the section name to the logFile
			fwrite($logFile, $sectionName);
			
			//This is a single string so just write the line to the file
			fwrite($logFile, $sectionData);

			//Insert carriage return
			fwrite($logFile, "\n");
			
			//We wrote the data return true
			return TRUE;
		}
		
		//Something happened if we go down here, so return an error
		return FALSE;
	}

	function closeLogFile($logFile) {
		/*
		 * This function will close the log file and rename it 
		 * to the appropriate name SerialNumber-YYYY-MM-DD-HH-MM.txt
		 * from the TempLogFile.txt
		 */
		
		global $logFile;
		global $validation_array;
		global $sortCode;

		//Determine the path that we will be using
		$path = getLogFilePath($sortCode);
		
		$newFileName = $path . $validation_array['machine_serial'] . '-' . date("Y-m-d-H-i", time()) . '.txt';
		
		fclose($logFile);
		
		$oldFileName = $path . 'TempLogFile.txt';
		
		if (! rename($oldFileName, $newFileName) ) {
			//Something happened during the rename
			return FALSE;
		}
		
	}
	
	function createAsteriskArray() {
		/*
		 * This function will open the text file that contains the character to asterisk
		 * representations and build them into the array $asteriskArray() 
		 */
		global $asteriskArray;
		global $logFile;
		
		$asteriskFilePath = dirname(__FILE__) . '/alpha.csv';
		// Open the file alpha.txt from the current directory
		$asteriskFile = fopen($asteriskFilePath, 'r');
		
		// Confirm that the file actually opened
		if (! $asteriskFile) {
			//File didn't open so return an error
			echo "File Open Error - alpha.csv does not exist!";
			
			return FALSE;
		}
		
		while (($tempArray = fgetcsv($asteriskFile)) !== FALSE) {
			//Create the temporary array to hold the line read from the file
						
			$tempString = '';

			foreach ($tempArray as $key=>$value) {

				if($key<2) {

					//These are the array parameters so skip them
					continue;

				} else {
					
					//Replace 0 with spaces and 1 with a #
					$tempString .= ($value == 0) ? ' ' : '#';
					
				}
			}
			
			//Now that we have the entire string create the array entry
			//$tempArray[0] is the letter and $tempArray[1] is the line number
			//of the letter
			$asteriskArray[$tempArray[0]][$tempArray[1]] = $tempString; 
			
		}
		
		//writeToLogFile("Display Asterisk Array", $asteriskArray, $logFile);
		
		return $asteriskArray;
	}
	
	function displayAsteriskMessage($message = NULL) {
		/*
		 * This function will load the asterisk file, and then process a message
		 * on the screen using the predefined character definitions
		 */
		global $asteriskArray;
		global $logFile;
		
		//writeToLogFile("Display Asterisk Array", $asteriskArray, $logFile);
		
		if(! $message) {
			//No message was passed so just return a false
			return FALSE;
		}
		
		if (count($asteriskArray) === 0 ) {
			if(! createAsteriskArray()) {
				//If the asterisk handler returned a failure then return false
				echo "Asterisk Handler failed to load - error!";
				return FALSE;
			}
		}

		$message = trim($message);
		
		//Determine the length of the message
		$lengthOfMessage = strlen($message);
		
		//Set the line count
		$lineCount = round(($lengthOfMessage / 12), 0, PHP_ROUND_HALF_UP);

		//Work to build the lines
		$currentLineNumber = 1;
		$currentCharacterNumber = 1;
		$currentDisplayLineNumber = 1;
		$currentCharacter = '';
		$displayMessage = Array();
		
		while ($currentLineNumber <= $lineCount && $currentCharacterNumber <= $lengthOfMessage) {
			//writeToLogFile("LOOP TO DISPLAY MESSAGE ","{$currentLineNumber} of {$lineCount}", $logFile);
			//writeToLogFile("LOOP TO DISPLAY MESSAGE ","{$currentCharacterNumber} of {$lengthOfMessage}", $logFile);
			if($currentCharacter % 12 === 1) {
				//We have reached the beginning of the next line so increment
				$currentLineNumber++;
			}

			$currentCharacter = strtoupper($message[($currentCharacterNumber - 1)]);

			for($i = 1; $i <= 8; $i++) {
				//Here we are going to loop through line of the character from 
				//the asterisk array
				if(! isset($displayMessage[$currentLineNumber][$i]) ) {
					$displayMessage[$currentLineNumber][$i] = '';
				}
				//Fix the space to allow for the lowercase s to be the identifier
				if($currentCharacter === " ") {
					$currentCharacter = "s";
				}
				//writeToLogFile("{$displayMessage[$currentLineNumber][$i]} =", "{$asteriskArray[$currentCharacter][$i]}", $logFile);
				
				$displayMessage[$currentLineNumber][$i] .= ' ' . $asteriskArray[$currentCharacter][$i]; 
			} 
			//Increment the Character Number
			$currentCharacterNumber++;
		}

		//Run a loop for each line of the message
		foreach($displayMessage as $lineKey => $lineValue) {
			
			//Run a loop for each asterisk line of the line of the message
			foreach($lineValue as $displayLine) {
				echo $displayLine;
				echo "\n";
			}
			echo "\n";
		}
		
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
		global $sortCode;
		
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
				echo "Are you at sort code {$sortCode}? \n";
				echo "You must enter either yes or no! \n";
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
		global $validation_array;
		
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
	
	function getPhysicalDiskBaseInformation($fdisk_output) {
		/*
		 * This function will take the output of the fdisk output and clean it up for the physical harddrive
		 * information to and create an array for the sizes of each disk along with the information for that disk
		 */
		foreach ($fdisk_output as $data) {
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
				$disk_array[] = substr($temp_disk[0], 5, 8);
			}
		}
		
		// Return all of the Physical Disks that were found
		return $disk_array;
	}
	
	function getPhysicalDiskSizeInformation($fdisk_output) {
		/*
		 * This function will take the output of the fdisk output and clean it up for the physical harddrive
		 * size information to and create an array for the sizes of each disk along with the information 
		 * for that disk
		 * 
		 * Parameter - $fdisk_output - array - output the FDisk command
		 * Returns - $size_arry - array - the separated harddrive size information
		 */
		foreach ($fdisk_output as $data) {
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
				$disk_array[] = substr($temp_disk[0], 5, 8);
				$size_array[] = cleanHDSize($temp_disk[1]);
			}
		}
		
		//Return all of the size information for the Physical Disks found
		return $size_array;
	}	
	
	function getVirtualDiskInformation($fdisk_output) {
		/*
		 * This function will take the fdisk output and pass through each line looking for partition entries
		 * and hands it all back in array form
		 * 
		 * Paramater - $fdisk_output - array - output the FDisk command
		 * Returns - $partition_array - array - the separated listing of each partition found on all physical disks
		 */
		foreach ($fdisk_output as $data) {
			/*
			 * Let's work through each individual line looking for disk
			 * entries or the partition runs
			 */
			$disk_entry = stripos($data, "Disk /");
			if ($disk_entry === FALSE) {
				//We did not find any disk entry so we are going to look for a partition entry
				$part_entry = stripos($data, "/dev/sd");
				
				if ($part_entry !== FALSE) {
					//We did find a partition entry so we are going to add it to the partition array
					$partition_array[] = substr($data, $part_entry, 9);
				}
			} elseif ($disk_entry !== FALSE) {
				//This is a physical hard drive not a partition so just continue
				continue;
			}
		}

		//We reached the end of the partition routine so hand back the partitions
		return $partition_array;
	}
	
	function getPhysicalDiskParamaters($disk_array, $partition_array, $size_array) {
		/*
		 * Now we need to utilize the hdparm -i command to show all drive
		 * serial numbers to allow for all drives to have been output. 
		 * We will utilize the array we built to run hdparm on up to 3 drives
		 * any more than 3 drives and they will be ignored
		 */
		
		global $logFile;
		
		$disk_pass = 1;
		if (array_count_values($disk_array) > 0) {
			foreach($disk_array as $key=>$disk) {
				//Build the actual shell command
				$exec_command = "sudo hdparm -i {$disk}";
				//Create the disk id variable
				$disk_id = substr($disk,(strlen($disk) - 3),3);
				//Create the dynamic variable for the hdparm output
				$hdparm_output = $disk_id . "_hdparm_output";
				//Create the dynamic variable for the hdparm return
				$hdparm_return = $disk_id . "_return";
				//Make output and return variables global

				global $$hdparm_output;
				global $$hdparm_return;
				
				//Display the command prior to running
				echo "Executing - {$exec_command} {$hdparm_output} {$hdparm_return} \n";

				//Execute the command
				exec($exec_command, $$hdparm_output, $$hdparm_return);
				
				/*
				 * Here we are going to process the actual HDPARM output data to be able to determine
				 * harddrive model and serial number - soon to be function processHdparmOutput
				 */
				$model_id = 'disk_model';
				$firmware_id = 'disk_fw';
				$serial_id = 'disk_serial';
				$count_id = 'disk_part_count';
				$size_id = 'disk_size';
				writeToLogFile("{$hdparm_output}", " {$$hdparm_return}", $logFile);
				if ($$hdparm_return == 0) {

					//Return value of 0 means a successful run - so process the output
					foreach($$hdparm_output as $data) {

						//Is this the line that has the data we are looking for - it should begin with Model
						$identification_entry = strpos($data, 'Model=');
						
						if ($identification_entry !== FALSE) {

							//This is the line we need
							//Separate the model, firmware, and serial number into an array
							$identification = explode(',', $data);
							
							$hdparm_array[$disk_pass][$model_id] = cleanHDIdentification($identification[0]);
							$hdparm_array[$disk_pass][$firmware_id] = cleanHDIdentification($identification[1]);
							$hdparm_array[$disk_pass][$serial_id] = cleanHDIdentification($identification[2]);
							$hdparm_array[$disk_pass][$count_id] = getPartitionCountForDisk($partition_array, $disk_id);
							$hdparm_array[$disk_pass][$size_id] = $size_array[$key];
						}
					} 
				} else {

					//A failed return value means the hard drive is most likely our USB drive
					//We need to create the values and put unknowns in there
					$hdparm_array[$disk_pass][$model_id] = 'Unknown (USB?)';
					$hdparm_array[$disk_pass][$firmware_id] = 'Unknown (USB?)';
					$hdparm_array[$disk_pass][$serial_id] = 'Unknown (USB?)';
					$hdparm_array[$disk_pass][$count_id] = getPartitionCountForDisk($partition_array, $disk_id);
					$hdparm_array[$disk_pass][$size_id] = $size_array[$key];										
				}
				//Increment the disk counter
				$disk_pass++;
			}
		}
		return $hdparm_array;	
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

	function fixPermissionsOnLogs() {
		/*
		 * This function will go through and fix the permissions on the log files
		 * this will allow the standard user to read them
		 */
		
		global $sortCode;
		global $logFile;
		
		$folderToChange = dirname(__FILE__) . "/{$sortCode}";
		$commandToExecute = "sudo chown -R diskwipe:users {$folderToChange}";
		
		exec($commandToExecute, $chmodOutput, $chmodReturn);
		
		if ($chmodReturn === 0) {
			writeToLogFile("Chown Return \n", $chmodReturn, $logFile);
			return TRUE;
		} else {
			writeToLogFile("Chown Return \n", $chmodReturn, $logFile);
			return FALSE;
		}
		
		
	}

	//Clear the screen
	passthru('clear');
		
	//This is the first run of the get sort code routine just to start the process
	getSortCode();
	
	//Create the logfile to capture all the data
	if (! createLogFile() ) {
		//Log File failed to be created
		echo "Log file couldn't be opened - aborting!";
		exit;
	}
	
	//Show the user that we have started the data gathering process
	echo "Parsing data for sort code - {$sortCode} \n";
	writeToLogFile("Parsing Begin", "Parsing data for sort code - {$sortCode}", $logFile);
	
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
	 * Here we will gather the physical disk information, physical disk size 
	 * information, and the virtual disk breakdowns by disk
	 */
	$disk_array = getPhysicalDiskBaseInformation($fdisk_output);
	$size_array = getPhysicalDiskSizeInformation($fdisk_output);
	$partition_array = getVirtualDiskInformation($fdisk_output);
	
	//See how many disks were found
	$num_of_disks = count($disk_array);
	$validation_array['total_disk_count'] = $num_of_disks;
	if ($num_of_disks > 0) {
		//Found some available disks so let's tell us about them
		writeToLogFile("Disk Array Information", "There are {$num_of_disks} disk(s) in this system", $logFile);
		writeToLogFile("", $disk_array, $logFile);
	}
	
	//See how many partitions were found
	$num_of_parts = count($partition_array);
	$validation_array['total_partition_count'] = $num_of_parts;
	if ($num_of_parts > 0) {
		//Found some partitions so let's tell us about them
		writeToLogFile("Partition Array Information", "There are {$num_of_parts} partition(s) in this system", $logFile);
		writeToLogFile("", $partition_array, $logFile);
	}
	
	//Log the size array information to the file
	writeToLogFile("Size Array Information", $size_array, $logFile);
	
	/*
	 * Let's get the physical disk information and insert it into our array
	 */
	$validation_array['disks'] = getPhysicalDiskParamaters($disk_array, $partition_array, $size_array);
	
	/*
	 * Initialize our RPC client object pasing in all appropriate variables
	 */
	$client = new XML_RPC_Client(xml_path, xml_server, xml_port, xml_proxy, xml_proxy_port, xml_proxy_user, xml_proxy_pass);
	/*
	 * Turn on debugging
	 */
	//$client->setDebug(1);
	/*
	 * Create a response object to catch the information returning from the XML_RPC
	 * 
	 */
	$response = $client->send(buildXMLRPCMessage($validation_array));
	
	writeToLogFile("Message Object", buildXMLRPCMessage($validation_array), $logFile);
	
	writeToLogFile("Response Object", $response, $logFile);

	writeToLogFile("Validation Array", $validation_array, $logFile);

	writeToLogFile("Fdisk Output", $fdisk_output, $logFile);

	writeToLogFile("Chassis Serial", $chassis_serial_number, $logFile);
	
	writeToLogFile("Baseboard Serial", $baseboard_serial_number, $logFile);
	
	writeToLogFile("System Serial", $system_serial_number, $logFile);

	if (isset($sda_return)) {
		if ($sda_return == 0) {	
			writeToLogFile("SDA HDPARM A", $sda_hdparm_output, $logFile);
			writeToLogFile("SDA RETURN A", $sda_return, $logFile);
		}
	}
	if (isset($sdb_return)) {
		if ($sdb_return == 0) {
			writeToLogFile("SDA HDPARM B", $sdb_hdparm_output, $logFile);
			writeToLogFile("SDA RETURN B", $sdb_return, $logFile);
					}
	}
	if (isset($sdc_return)) {
		if ($sdc_return == 0) {
			writeToLogFile("SDA HDPARM C", $sdc_hdparm_output, $logFile);
			writeToLogFile("SDA RETURN C", $sdc_return, $logFile);
		}
	}
	
	
	//Clear the screen
	passthru('clear');
	
	//Get the display message to be sent to the screen
	$display_array = verifyDiskWipe();	

	//Now display the message on the screen
	displayNormalMessage($display_array);

	fixPermissionsOnLogs();
	
	closeLogFile($logFile);

	

/*
 * End of file: DiskWipeVerify.php
 * Location: /DiskWipeVerify.php
 */
?>