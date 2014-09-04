<?php
//not sure if i need DB.php here i shouldn't - but locally i do otherwise it doesn't like me
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$title = $_POST['title'];
$content = $_POST['content'];
$fullname = $_SESSION['userfullname'];
$stnid = $_SESSION['user_id'];
$question = new PCRHandler();
$question->getQuestion(2)->addNewQuestion($title, $content, $stnid, $fullname);
if($question!=null){
header('Location: help.php');
}

?>