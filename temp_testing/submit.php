<?php

//$out = array();
//echo "Result: " . $result
//foreach($out as $line) {
    //echo $line;
//}

exec("tester.sh", $out, $result);

echo "Result: " . $result;
echo "Output: " . $out;

?>