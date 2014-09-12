<?php
require_once 'includes/db.php';
require_once 'includes/handlers.php';
//Post variables to add new question with params
$title = $_POST['title'];
$content = $_POST['content'];
$fullname = $_SESSION['userfullname'];
$stnid = $_SESSION['user_id'];
//Add the new question to the DB
$question = new PCRHandler();
$question->getCourse()->addNewQuestion($title, $content, $stnid, $fullname);
header('Location: help.php');
?>