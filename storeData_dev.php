<?php
    //Include the class and database functions
    require_once 'includes/handlers.php';
    /*
    Gets the variables passed through the AJAX call and stores them in the database
    
    TODO: handle the submission ID and permissions
    */
    echo $_GET['reviews'];
    /*
    $startIndex = $_GET['startIndex'];
    $startLine = $_GET['startLine'];
    $endIndex = $_GET['endIndex'];
    $endLine = $_GET['i'];
    $annotationText = $_GET['comment']; 
    $stnid = $_SESSION['user_id'];
    $id = $_SESSION['id'];
    $review = new PCRHandler();
    $review->getReview($stnid, $id, $annotationText, $anchor, $focus);*/
?>
