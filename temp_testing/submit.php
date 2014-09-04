<?php



exec("tester.sh", $out, $result);

echo "Result: " . $result . "\n";

$out = array();
echo "Result: " . $result
foreach($out as $line) {
    echo $line;
}

?>