<?php
require_once 'includes/handlers.php';
$title = $_POST['title'];
$content = $_POST['content'];
$opendate = $_POST['open'];
$question = new PCRHandler();
$question->getQuestion($_SESSION['course_id'])->addNewQuestion($title, $content);
//Question added, go back to help.php where you can see it (will add confirmation on other page somewhere down the track + edit)
header('Location: help.php');
?>