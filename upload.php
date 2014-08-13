<?php

$ext = array("zip", "rar");
$temp = explode(".", $_FILES["file"]["name"]);
$f_ext = end($temp);

if(in_array($f_ext, $ext)) {
	if($_FILES["file"]["error"] > 0) {
		//Error
	} else {
		
	}
}