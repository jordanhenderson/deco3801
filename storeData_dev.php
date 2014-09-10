<?php
require_once 'includes/db.php';
require_once 'includes/handlers.php';
$anchor = $_GET['anchor'];
$focus = $_GET['focus'];
$annotationText = $_GET['annotation'];
$stnid = $_SESSION['user_id'];
$id = $_SESSION['id'];
echo "here: " . $anchor . ", " . $focus . ", " . $annotationText . ", " . $stnid . ", " . $id;
$review = new PCRHandler();
echo "\nWe be here matey";
$review->getReview($stnid, $id, $annotationText, $anchor, $focus);
echo "\nWe be here matey";
?>