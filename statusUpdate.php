<?php
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$id = $_GET['id'];
$resolve = new PCRHandler();
if($_SESSION['Status'] == 0){
$resolve->getQuestion($id)->markResolved($id);
}
else {
	$resolve->getQuestion($id)->markUnresolved($id);
}
header('Location: help.php');
?>