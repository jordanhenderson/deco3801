<?php
require_once 'includes/db.php';
require_once 'includes/handlers.php';
//Get the ID of the question, and remove the question from the DB
//Where the ID is this
$id = $_GET['id'];
$delete = new PCRHandler();
$delete->getQuestion($id)->removeQuestion($id);
header('Location: help.php');
?>