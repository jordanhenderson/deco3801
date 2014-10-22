<?php
error_reporting(E_ALL);

class javaTesting {
	private $source_directory;
	private $test_class_path;
	private $test_class_name;
	
	public function __construct($source_directory, $test_class_path, $test_class_name) {
		$this->source_directory = $source_directory;
		$this->test_class_path = $test_class_path;
		$this->test_class_name = $test_class_name;
	}

	/**
	* This function takes a directory containing java source files and compiles them.
	* It will return true if the compilation was successful, false otherwise.
	* 
	*/
	public function compile() {
		$scriptOutput = shell_exec("javac " . $this->source_directory . " *.java");

		echo "=== Compilation output ===\n" . $scriptOutput . "=======================\n";
	}

	/**
	* This function takes a JUnit test file as input and runs it. If all tests
	* pass, it will return true. Otherwise, it will return an array of test 
	* names and results in the following format
	*
	* e.g. $failedTests[0] == "test_name:expected_result:actual_result"
	*/
	public function runJUnitTest() {
		$scriptOutput = shell_exec("cd " . $this->test_class_path . " && java " . $this->test_class_name);

		echo "=== Execution output ===\n" . $scriptOutput . "=======================\n";

		if ($scriptOutput == "true") { // All tests passed
			return true;
		} else {
			$counter = 0;

			// Some tests failed, parse output to find names of failed tests
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $scriptOutput) as $line){
    			if (strpos($line, 'expected') !== FALSE) {
				    // Lines contained the string "expected" are lines with failing test names
    				$testName = substr($line, 0, strpos($line, "("));
    				$expectedResult = substr($line, strpos($line, "expected:") + 9, strpos($line, " but ") - (strpos($line, "expected:") + 9));
    				$actualResult = substr($line, strpos($line, "was:") + 4);
    				echo "Failed test: " . $testName . " -- Expected: " . $expectedResult . " -- Actual: " . $actualResult . PHP_EOL;

    				$failedTests[$counter++] = $testName . ":" . $expectedResult . ":" . $actualResult;
				}
			}

			return $failedTests;
		}
	}
}

class bashTesting {
	private $test_file_location;
	private $assignment_file_location;

	public function __construct($test_file_location, $assignment_file_location) {
		$this->test_file_location = $test_file_location;
		$this->assignment_file_location = $assignment_file_location;
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
		$scriptOutput = shell_exec("bash " . $this->test_file_location . " " . $this->assignment_file_location);
		//echo(shell_exec("echo $?"));
		
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
