<?php

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
	
/*
 * End of File: logging_functions.php
 * Class: None
 * File: ./includes/logging_functions.php
 */