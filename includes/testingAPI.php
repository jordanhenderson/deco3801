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
	* 		$testResults[0] == "pass"
	*		$testResults[1] == "fail"
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

		$couter = 0;

		foreach ($testResults as $individualResult) {
			$endResult = explode(":", $individualResult);

			echo "Test Number: " . $endResult[0] . " - " . $endResult[1] . PHP_EOL;

			$counter++;
			$testOutput[$counter] = $endResult[1];
		}

		return $testResults;
	}

	/**
	* This function will insert the given test results into the database
	*/
	public static function dbInsertTestResults($testResults) {

	}
}

?>