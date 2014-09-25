<?php
echo "starting submission process..." . PHP_EOL;

error_reporting(E_ALL);

require_once '../includes/testingAPI.php';

//$tester = new functionalTestAPI();

echo "executing bash script..." . PHP_EOL;
$output = functionalTestAPI::executeBashScript("../../upload/tester.sh");

foreach ($output as $value) {
	echo $value . PHP_EOL;
}

echo "updating test restults..." . PHP_EOL;
functionalTestAPI::dbUpdateTestResults(00001, $output);


echo "compiling java files" . PHP_EOL;
functionalTestAPI::compileJavaSubmission("../../upload/");

echo "running junit tests..." . PHP_EOL;
$output = functionalTestAPI::runJUnitTestFile("../../upload/", "TestRunner");

foreach ($output as $value) {
	echo $value . PHP_EOL;
}

?>
