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
    echo "<pre>";
    /*print_r($reviews);
    echo "</pre>";*/
    foreach ($reviews as $review) {
        /* Id of reviewer, submission id, start position, start line, 
            end position, end line, comment contents, filename
        */
        print_r($review);
        $reviewObject = $handler->getReview($stnid, $id, $review->startIndexSet, $review->startLine, '' . $review->comment,
        '' . $review->text, $review->reviewID, '' . $review->fileName);
        $reviewObject->isValid();
    }
    echo "</pre>";
?>
