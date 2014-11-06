<?php

class bashTesting {
	private $test_file_location;
	private $test_dir;

	public function __construct($test_file_location, $test_dir) {
		$this->test_file_location = $test_file_location;
		$this->test_dir = $test_dir;
	}

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
	* 
	* Script should have an exit code of 0, indicating success. Any other 
	* exit code will be interpreted as a failure and not release any
	* of the results.
	*
	* Script should have global executable permissions to ensure
	* it can run
	* 
	* Returns null if there were no test results
	*/
	public function execute() {
		// Execute student assignment
		chdir($this->test_dir);
		$scriptOutput = shell_exec($this->test_file_location . "run.sh");
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
}

?>
