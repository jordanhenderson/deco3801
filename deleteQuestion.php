<?php
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$id = $_GET['id'];
$delete = new PCRHandler();
$delete->getQuestion($id)->removeQuestion($id);
header('Location: help.php');
?>