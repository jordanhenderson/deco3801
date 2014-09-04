<?php

error_reporting(E_ALL);

exec("./tester.sh", $out, $result);

$out = array();

echo "Result: " . $result . PHP_EOL;

foreach($out as $line) {
    echo $line;
}

?>