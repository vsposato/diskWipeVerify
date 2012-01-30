<?php

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
			if($currentCharacterNumber % 12 === 1) {
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

/*
 * End of File: asterisk_functions.php
 * Class: None
 * File: ./includes/asterisk_functions.php
 */