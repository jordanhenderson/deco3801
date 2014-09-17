<?php
error_reporting(E_ALL);

require_once '../includes/testingAPI.php';

$tester = new functionalTestAPI();

$output = $tester->executeBashScript("../../upload/tester.sh");
foreach ($output as $value) {
	echo $value . PHP_EOL;
}

?>
