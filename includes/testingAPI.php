<?php

require_once '../includes/db.php';
error_reporting(E_ALL);

class functionalTestAPI {

	/**
	* This function will return an array of values indicating whether the test
	* with that index succeeded or not.
	* 
	* i.e. if test 0 passes, 
	*
	* Script must be in web server's upload directory
	*/
	public function executeBashScript($script_name) {
		// Execute student assignment
		$resultOutput = shell_exec("../../upload/" . $script_name);

		//echo "Result: " . $resultOutput . PHP_EOL;

		$testResults = explode(";", $resultOutput);

		foreach ($testResults as $individualResult) {
			$endResult = explode(":", $individualResult);

			echo "Test Number: " . $endResult[0] . PHP_EOL;
			echo "Test Result: " . $endResult[1] . PHP_EOL;
		}

		if ($testResults == "" || $testResults == null) {
			return null;
		}

		return $testResults;
	}
}

?>