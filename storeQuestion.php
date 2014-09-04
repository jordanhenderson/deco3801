<?php
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$title = $_POST['title'];
$content = $_POST['content'];
$fullname = $_SESSION['userfullname'];
$stnid = $_SESSION['user_id'];
$question = new PCRHandler();
$question->getCourse()->addNewQuestion($title, $content, $stnid, $fullname);
//if($question!=null){
//
//}
//this is TEMPORARY FIX until i work out why the main thing isn't working....even though it was
/*$con = mysqli_connect("localhost","deco3801","hh2z2WG2q","deco3801") or
    die("Error:".mysqli_error($con));
-	$sql = "INSERT INTO `deco3801`.`Question` (`StudentID`, `CourseID`, `StudentName`, `Title`, `Content`, `Status`) VALUES ('".$stnid."', '2', '".$fullname."', '".$title."', '".$content."', '0');";
-	$query = mysqli_query($con, $sql);
-	mysqli_close($con);*/
header('Location: help.php');
?>