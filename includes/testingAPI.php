<?php
error_reporting(E_ALL);

require_once 'db.php';

class functionalTestAPI {

	/**
	* This function will run the given bash script on the server, then return an 
	* array of values indicating whether the test with that index succeeded 
	* or not. 
	* 
	* i.e. if test 1 passes and test 2 fails,
	* 		$testOutput[0] == "pass"
	*		$testOutput[1] == "fail"
	* 
	* Script output format should be "test_number:pass/fail" without quotes, 
	* separated by semi-colons. e.g. "1:pass;2:fail;3:pass"
	*/
	public static function executeBashScript($script_path) {
		// Execute student assignment
		$scriptOutput = shell_exec($script_path);
		
		// Check for newline at the end, remove if present
		if (substr($scriptOutput, -1) == "\n") {
			$scriptOutput = substr($scriptOutput, 0, -1);
		}

		// Make sure the last char is not semi-colon for easier manipulation later
		if (substr($scriptOutput, -1) == ";") {
			$scriptOutput = substr($scriptOutput, 0, -1);
		}

		$testResults = explode(";", $scriptOutput);

		if ($testResults == "" || $testResults == null) {
			return null;
		}

		$counter = 0;

		foreach ($testResults as $individualResult) {
			$endResult = explode(":", $individualResult);

			// TODO remove this after tests are created. For debugging purposes only
			//echo "Test Number: " . $endResult[0] . " - " . $endResult[1] . PHP_EOL;

			$counter++;
			$testOutput[$counter] = $endResult[1];
		}

		return $testOutput;
	}

	/**
	* This function will update the given test results for the database submission entry.
	* It will return true if the data is inserted successfully, false otherwise
	* 
	* $testResults must be an array with each element containing the value 
	* "pass" or "fail"
	*/
	public static function dbUpdateTestResults($submissionID, $testResults) {
		$dbString = "";

		// Test resuts must be in string format to store in database
		foreach ($testResults as $value) {
			$dbString = $dbString . "," . $value;
		}

		$dbString = substr($dbString, 1);

		echo "dbString:" . $dbString;

		$db = new Database();

		if ($db->query("UPDATE Submission SET Results='$dbString' WHERE SubmissionID='$submissionID'") != false) {
			echo "Test data inserted successfully\n";
			return true;
		} else {
			echo "Error entering test results into database\n";
			return false;
		}
	}

	/**
	* This function will retrieve the test results for submission from the database.
	* 
	* This function will return an array with each element containing the value 
	* "pass" or "fail"
	*/
	public static function dbRetrieveTestResults($submissionID) {
		if ($db->query("SELECT Results FROM Submission WHERE SubmissionID='$submissionID'") != false) {
			echo "Test data retrieved successfully\n";
		} else {
			echo "Error retrieving test results from database\n";
		}
	}

	/**
	* This function will add the given script to the queue of files to be executed
	* by the server
	*/
	public static function addToQueue($script_path) {

	}
}

?>