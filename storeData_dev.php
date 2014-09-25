<?php
    //Include the class and database functions
    require_once 'includes/handlers.php';
    /*
    Gets the variables passed through the AJAX call and stores them in the database
    
    TODO: handle the submission ID and permissions
    */
    //$reviews = json_decode($_GET['reviews']);
    $reviews = $_GET['reviews'];
    $crs = new PCRHandler();
    //$stnid = $_SESSION['user_id'];
    //$id = $_SESSION['id'];
    $crs->saveReviews($reviews);
    
    /*
    foreach ($reviews as $review) {
        /* Id of reviewer, submission id, assignment id, start position, start line, 
            end position, end line, comment contents, filename
        */
      /*  $reviewObject = $crs->addReview($stnid, $id, '00003', $review->startIndexSet, $review->startLine, '' . $review->comment,
        '' . $review->text, $review->reviewID, '' . $review->fileName);
        
    }*/
?>