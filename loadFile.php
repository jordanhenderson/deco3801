<?php
    /*
    Retrieves the file from the server and returns it to the calling page i.e. 
    review.php. This code will be deleted and replaced by loadFile.php once 
    we remove the current storage method using AnnotateIt
    */
    
	$filename = $_GET['filename'];
	$assignment = "/var/www/upload/course_00001/assign_00001/submissions/s1234567/" . $filename;
	$handle = fopen($assignment, "r");
	$contents = fread($handle, filesize($assignment));
	echo "<code>" . $contents . "</code>";
	fclose($handle);
?>