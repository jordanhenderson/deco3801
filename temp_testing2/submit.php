<?php
error_reporting(E_ALL);

require_once '../includes/testingAPI.php';

//$tester = new functionalTestAPI();

$output = functionalTestAPI::executeBashScript("../../upload/tester.sh");
foreach ($output as $value) {
	echo $value . PHP_EOL;
}

functionalTestAPI::dbUpdateTestResults(00001, $output);

?>
