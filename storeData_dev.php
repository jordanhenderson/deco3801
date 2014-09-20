<?php
    //Include the class and database functions
    require_once 'includes/handlers.php';
    /*
    Gets the variables passed through the AJAX call and stores them in the database
    
    TODO: handle the submission ID and permissions
    */
    $anchor = $_GET['anchor'];
    $focus = $_GET['focus'];
    $annotationText = $_GET['annotation'];
    $stnid = $_SESSION['user_id'];
    $id = $_SESSION['id'];
    $review = new PCRHandler();
    $review->getReview($stnid, $id, $annotationText, $anchor, $focus);
?>
