<?php
echo "starting submission process..." . PHP_EOL;

error_reporting(E_ALL);

require_once '../includes/testingAPI.php';

//$tester = new functionalTestAPI();

echo "executing script..." . PHP_EOL;
$output = functionalTestAPI::executeBashScript("../../upload/tester.sh");

foreach ($output as $value) {
	echo $value . PHP_EOL;
}

echo "updating test restults..." . PHP_EOL;
functionalTestAPI::dbUpdateTestResults(00001, $output);

?>
