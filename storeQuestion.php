<?php
//not sure if i need DB.php here i shouldn't - but locally i do otherwise it doesn't like me
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$title = $_POST['title'];
$content = $_POST['content'];
$opendate = $_POST['open'];
$fullname = $_SESSION['userfullname'];
$stnid = $_SESSION['user_id'];
$question = new PCRHandler();
$question->getQuestion($_SESSION['course_id'])->addNewQuestion($title, $content, $stnid, $fullname);
if($question!=null){
header('Location: help.php');
}

?>