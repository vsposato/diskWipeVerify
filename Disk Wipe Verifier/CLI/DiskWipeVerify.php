<?php

	/*
	 * Set some global variables for use throughout
	 */
	global $sortCode;
	global $logFile;


	/*
	 * Implement the PEAR package for XML_RPC for communication with the backend server
	 */
	require_once('XML/RPC.php');
	
	/*
	 * Implement the general_functions, asterisk_functions, and logging_functions for use throughout 
	 * the process.
	 */
	require_once('includes/general_functions.php');
	require_once('includes/asterisk_functions.php');
	require_once('includes/logging_functions.php');
	
	/*
	 * Include the machine class so that it can be instantiated later
	 */
	require_once('includes/Machine.Class.php');
	
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
	define("xml_server", "www.nettechconsultants.com");
	define("xml_port", 80);
	define("xml_proxy", NULL);
	define("xml_proxy_port", NULL);
	define("xml_proxy_user", NULL);
	define("xml_proxy_pass", NULL);


	// Clear the screen
	passthru('reset');
	
	// Get the site code
	setSortCode();

	//Create the logfile to capture all the data
	if (! createLogFile() ) {
		//Log File failed to be created
		echo "Log file couldn't be opened - aborting!";
		exit;
	}
	
	//Show the user that we have started the data gathering process
	echo "Parsing data for sort code - {$sortCode} \n";
	writeToLogFile("Parsing Begin", "Parsing data for sort code - {$sortCode}", $logFile);
	
	// Create a new machine
	$checkWorkstation = new Machine();
	
	//Get the display message to be sent to the screen
	$validation_array = verifyDiskWipe();	

	//Now display the message on the screen
	displayNormalMessage($validation_array);

	// Turn the validation array into well-formed XML to hand off
	$message = createXMLFromArray($validation_array);
	
	// Call the Transmission function
	$response = transmitXMLMessageToPOST($message,xml_server);
	
	var_dump($response);
	/*
	 * Initialize our RPC client object pasing in all appropriate variables
	 */
	//$client = new XML_RPC_Client(xml_path, xml_server, xml_port, xml_proxy, xml_proxy_port, xml_proxy_user, xml_proxy_pass);
	/*
	 * Turn on debugging
	 */
	//$client->setDebug(1);
	/*
	 * Create a response object to catch the information returning from the XML_RPC
	 * 
	 */
	//$response = $client->send(buildXMLRPCMessage($message));
	
	// Dump the display array
	//print_r($validation_array);
	
	// Dump the object to the screen
	writeToLogFile("Main Script ",objectToArray($checkWorkstation),$logFile);

	// Close the log file
	closeLogFile($logFile, $checkWorkstation->getSerialNumber());
	
/*
 * End of File: DiskWipeVerify.php
 * Class: Main (No Class)
 * File: DiskWipeVerify.php
 */