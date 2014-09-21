<?php
    //Include the class and database functions
    require_once 'includes/handlers.php';
    /*
    Gets the variables passed through the AJAX call and stores them in the database
    
    TODO: handle the submission ID and permissions
    */
    $reviews = json_decode($_GET['reviews']);
    echo '<pre>';
    print_r($reviews[0]);
    echo '</pre>';
    $handler = new PCRHandler();
    $stnid = $_SESSION['user_id'];
    $id = $_SESSION['id'];
    foreach ($reviews as $review) {
        /* Id of reviewer, submission id, start position, start line, 
            end position, end line, comment contents, filename
        */
        $handler->getReview($stnid, $id, $review[0], $review[1], $review[2],
        $review[3], $review[4], $review[5]);
    }
    unset($review);
    /*
    $startIndex = $_GET['startIndex'];
    $startLine = $_GET['startLine'];
    $endIndex = $_GET['endIndex'];
    $endLine = $_GET['i'];
    $annotationText = $_GET['comment']; 
    
    
    $review = new PCRHandler();
    $review->getReview($stnid, $id, $annotationText, $anchor, $focus);*/
?>
