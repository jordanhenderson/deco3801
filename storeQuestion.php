<?php
//not sure if i need DB.php here i shouldn't - but locally i do otherwise it doesn't like me
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$title = $_POST['title'];
$content = $_POST['content'];
$stnid = $_SESSION['user_id'];
$fullname = $_SESSION['userfullname'];
echo $_SESSION['course_id'];
echo $title;
echo $content;
echo $fullname;
echo $stnid;
$question = new PCRHandler();
$question->getQuestion($_SESSION['course_id'])->addNewQuestion($title, $content, $stnid, $fullname);
if($question!=null){
header('Location: help.php');
}

?>