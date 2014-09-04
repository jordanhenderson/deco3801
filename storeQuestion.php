<?php
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$title = $_POST['title'];
$content = $_POST['content'];
$fullname = $_SESSION['userfullname'];
$stnid = $_SESSION['user_id'];
//$question = new PCRHandler();
//$question->getQuestion($_SESSION['course_id'])->addNewQuestion($title, $content, $stnid, //$fullname);
//if($question!=null){
//
//}
//local test
$con = mysqli_connect("localhost","deco3801","hh2z2WG2q","deco3801") or
    die("Error:".mysqli_error($con));
-	$sql = "INSERT INTO `deco3801`.`question` (`QuestionID`, `StudentID`, `CourseID`, `StudentName`, `Opendate`, `Title`, `Content`, `Status`) VALUES (NULL, \'2\', \'2\', \'aname\', CURRENT_TIMESTAMP, \'atitle\', \'acontent\', \'0\');";

-	$query = mysqli_query($con, $sql);
-	mysqli_close($con);
header('Location: help.php');
?>