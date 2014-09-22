<?php
    //Include the class and database functions
    require_once 'includes/handlers.php';
    /*
    Gets the variables passed through the AJAX call and stores them in the database
    
    TODO: handle the submission ID and permissions
    */
    $reviews = json_decode($_GET['reviews']);
    $handler = new PCRHandler();
    $stnid = $_SESSION['user_id'];
    $id = $_SESSION['id'];
    // delete the reviews for the submission/file
    $sub = $handler->getSubmission('00002');
    $arr = $sub->getFiles();
    echo "<pre>";
    foreach ($arr as $rev) {
        print_r($rev);
    }
    echo "</pre>";
    foreach ($reviews as $review) {
        /* Id of reviewer, submission id, start position, start line, 
            end position, end line, comment contents, filename
        */
        $reviewObject = $handler->getReview($stnid, $id, $review->startIndexSet, $review->startLine, '' . $review->comment,
        '' . $review->text, $review->reviewID, '' . $review->fileName);
        $reviewObject->isValid();
    }
?>