<?php

	function createXMLFromArray($displayArray) {
		/*
		 * This will take the display array and turn it into well-formed XML
		 * following the HP POST standard
		 */
		
		// Create base XML string
		$xmlString = <<<XML
		<diskwipeinfo></diskwipeinfo>
XML;
		
		// Create a new SimpleXMLElement
		$xmlDocument = simplexml_load_string($xmlString);
		$xmlDocument->addAttribute("diskcount", $displayArray['disk_count']);
		$xmlDocument->addAttribute("serialnumber", $displayArray['machine_serial']);
		$xmlDocument->addAttribute("sortcode", $displayArray['sort_code']);
		$xmlDocument->addAttribute("wipe_status", $displayArray['wipe_status']);
		$xmlDocument->addAttribute("wipe_method", $displayArray['wipe_method']);
		
		$disksInformation = $xmlDocument->addChild("disks");
		
		foreach ($displayArray['disks'] as $key=>$disk) {
			$individualDisk = $disksInformation->addChild("disk");
			$individualDisk->addAttribute("id",$key);
			$individualDisk->addAttribute("wipe_method",$disk['wipemethod']);
			$individualDisk->addAttribute("wipe_status",$disk['wipestatus']);
			$individualDisk->addAttribute("sn",$disk['serial']);
		}

		
		return preg_replace('/version="1.0"/','version="1.0" encoding="UTF-8"',$xmlDocument->asXML());
		
			
	}
	
	function transmitXMLMessageToPOST($xmlMessage, $postURL) {
		/*
		 * This will take a pre-formed XML Message and transmit the file via cURL to 
		 * HP's POST system
		 */
		
		// This will initiate a cURL session with POST
		$curlHandle = curl_init();
		$contentType = array('Content-Type: text/xml');

		// Set the options for our CURL handler
		curl_setopt($curlHandle, CURLOPT_URL, $postURL);				// Set the URL for HP's POST
		curl_setopt($curlHandle, CURLOPT_FORBID_REUSE, true);			// Force cURL to close session and not allow reuse
		curl_setopt($curlHandle, CURLOPT_POST, true);					// Force regular POST to the URL
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);			// Return return value of curl_exec() as a string instead of outputting it
		curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 60);			// Set the connection timeout to be 60 secs
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 180);					// Set the process timeout to be 180 secs
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $xmlMessage);		// Set the POST fields to be the xmlMessage that was handed in
		curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $contentType);		// Set the content type to be XML
		
		// Submit the cURL request and capture the response
		$postResponse = curl_exec($curlHandle);
		
		// Close the cURL handler
		curl_close($curlHandle);
			
		// Pass the response back to the calling function
		return $postResponse;
		
	}
	
	function checkInternetConnectivity() {
		/*
		 * This function will use a grep of the ifconfig output to determine how many IP addresses are available.
		 * We know from experience that 1 IP address means that it is 127.0.0.1 only, and therefore not on the internet.
		 * However, we will display active IP addresses if they exist
		 */
		
		$tempIPInformation = array();
		
		exec('sudo ifconfig | grep -c "inet addr"', $tempIPInformation);
		
		if ($tempIPInformation[0] > 1) {
			return true;
		} elseif ($tempIPInformation[0] <= 1) {
			return false;
		}
	}

	function displayIPAddress() {
		/*
		 * This function will strip out the IP Address of the machine for display on screen.
		 * This will be for troubleshooting purposes only, and not really for anything else.
		 */
		
		// Setup an empty array to capture the data from the ifconfig command
		$tempIPInformation = array();
		
		// Run ifconfig and first grep out the lines with inet addr, and then of those lines the ones that are not localhost, and then break them into IP addresses only
		exec('sudo ifconfig | grep "inet addr" | grep -v "127.0.0.1" | grep -o "[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}"', $tempIPInformation);
		
		// We know from experience with ifconfig that the first IP address will be the one we want
		return $tempIPInformation[0];
		
	}
	
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

	function traverseArray($iterator, $level = 0) {
		/*
		 * This function will utilize the RecursiveArrayIterator class from PHP to work through the entire array
		 * provided. The function takes an iterator object as it's input
		 */	

		// Define some variables here
		$indentPrint = str_repeat("   ", $level);
		
		// Loop through the iterator until the valid method no longer returns true
		while ($iterator->valid()) {
			
			// Check to see if the value provided is an array
			if ($iterator->hasChildren()) {
				// This key has an array in it, so we need to make this a heading
				echo $indentPrint . $iterator->key() . PHP_EOL;
				// Now re-call the function with an incremented level
				traverseArray($iterator->getChildren(), ++$level);
			} else {
				//This has does not have an array in it, so just echo out the information
				echo $indentPrint . $iterator->key() . ' = ' . $iterator->current() . PHP_EOL;
			}
			// Go to next item in the array iterator
			$iterator->next();
		}
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
			
			// Create a new instance of the Recursive Array Iterator
			$displayIterator = new RecursiveArrayIterator($display_array);
			
			// Apply the iterator to the newly created iterator
			iterator_apply($displayIterator, 'traverseArray', array($displayIterator, 0));
			
		} elseif ($display_array['wipe_status'] == 'PASSED') {

			//Write to the log file a passed message
			writeToLogFile("Overall Verification Passed", "No disks had active partitions", $logFile);
			
			// Create a new instance of the Recursive Array Iterator
			$displayIterator = new RecursiveArrayIterator($display_array);
			
			// Apply the iterator to the newly created iterator
			iterator_apply($displayIterator, 'traverseArray', array($displayIterator, 0));

			echo "\n";
			
			// Since this was a passed Gdisk - we need to display the Serial Number in big letters
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

	function confirmInternetConnectivityFailed() {
		/*
		 * This function will be called by the primary routine only if the internet connectivity
		 * test failed. We will basically be letting the technician know that there is no internet
		 * connectivity, and he needs to confirm.
		 */
		
		echo "You currently do not have a valid IP address. If you have been instructed to work in offline mode \n";
		echo "please confirm by typing 'yes' at the prompt. Otherwise, type 'no' and we will shutdown and allow \n";
		echo "you to check your network connectivity. \n";
		echo "Are you sure you want to operate in offline mode? [yes / no] \n";

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
				echo "Are you sure you want to operate in offline mode? [yes / no] \n";
				echo "You must enter either yes or no! \n";
			}
			} while (($answer != "YES")  AND ($answer != "NO"));
			
			//The loop exited because the user said yes or no,
			//If he said yes confirm that he understands the ramifications.
			if ($answer == "YES") {
				// We need to make sure the technician is aware of the need to get a picture
				// of the screen to confirm diskwipe status.
				passthru("reset");
				echo "It is imperative that you get a picture of the screen confirming that the diskwipe went through \n";
				echo "otherwise failure to do so could result in termination. \n";
				sleep(10);
			}
			//The loop exited because the user said yes or no,
			//if he said no then shutdown the machine so that the technician can verify connectivity
			if ($answer == "NO") {
				// Shutdown this bo
				shell_exec("sudo shutdown -h now");
			}
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