<?php
    /*
    Retrieves the file from the server and returns it to the calling page i.e. 
    review_dev.php. This code will replace loadFile.php once we remove the 
    current storage method using AnnotateIt
    
    TODO: Investigate whether this code is necessary due to functions 
    contained in handlers.php -->
    may be able to use or include a function in the handlers file
    */
    $filename = $_GET['filename'];
	$assignment = "/var/www/upload/course_00001/assign_00001/submissions/s1234567/" . $filename;
	$handle = fopen($assignment, "r");
	$contents = fread($handle, filesize($assignment));
    $contents = str_replace('<', '&lt;', $contents);
    $contents = str_replace('>', '&gt;', $contents);
	echo $contents;
	fclose($handle);
?>