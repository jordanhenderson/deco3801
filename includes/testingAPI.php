<?php
error_reporting(E_ALL);
echo "testingAPI";
/*public class functionalTestAPI {
	/**
	* This function will update the given test results for the database submission entry.
	* It will return true if the data is inserted successfully, false otherwise
	* 
	* $testResults must be an array with each element containing the value 
	* "pass" or "fail"
	*
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
	*
	public static function dbRetrieveTestResults($submissionID) {
		if ($db->query("SELECT Results FROM Submission WHERE SubmissionID='$submissionID'") != false) {
			echo "Test data retrieved successfully\n";
		} else {
			echo "Error retrieving test results from database\n";
		}
	}
}*/

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
	public static function compile() {
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
	public static function runJUnitTest() {
		$scriptOutput = shell_exec("cd " . $this->test_class_path . " && java " . $this->test_class_name;

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
	public $test_file_location;
	public $assignment_file_location;

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
	* Returns null if there were no test results
	*/
	public static function execute() {
		// Execute student assignment
		$scriptOutput = shell_exec($this->test_file_location);
		
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