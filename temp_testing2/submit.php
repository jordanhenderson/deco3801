<?php
error_reporting(E_ALL);

require_once '../includes/testingAPI.php';

$tester = new functionalTestAPI();

$tester->executeBashScript("../../upload/tester.sh");

?>
