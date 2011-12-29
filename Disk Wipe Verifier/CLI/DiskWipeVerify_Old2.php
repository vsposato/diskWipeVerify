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