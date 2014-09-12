<?php

require_once 'includes/db.php';

error_reporting(E_ALL);

// Execute student assignment
$out = shell_exec("../../upload/tester.sh");

echo "Result: " . $out . PHP_EOL;

// Handle results and insert into database


?>
