<?php
	$filename = $_GET['filename'];
	// Fix this up. May not need it at all because of handlers functions
	$assignment = "/var/www/upload/course_00001/assign_00001/submissions/s1234567/" . $filename;
		$handle = fopen($assignment, "r");
		$contents = fread($handle, filesize($assignment));
		echo "<code>" . $contents . "</code>";
		fclose($handle);
?>