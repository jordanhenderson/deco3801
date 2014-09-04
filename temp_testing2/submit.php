<?php

exec("./tester.sh", $out, $result);

$out = array();

echo "Result: " . $result . PHP_EOL;

foreach($out as $line) {
    echo $line;
}

?>