<?php
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$content = $_POST['content'];
$fullname = $_SESSION['userfullname'];
$stnid = $_SESSION['user_id'];
$id = $_SESSION['id'];
$comment = new PCRHandler();
$comment->getComment($id, $content, $stnid, $fullname);
header('Location: displayQuestion.php?id='.$id);
?>