<?php

exec("tester.sh", $out, $result);

echo "1Result: " . $result . PHP_EOL;

$out = array();
echo "Result: " . $result
foreach($out as $line) {
    echo $line;
}

?>