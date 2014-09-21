<?php
    //Include the class and database functions
    require_once 'includes/handlers.php';
    /*
    Gets the variables passed through the AJAX call and stores them in the database
    
    TODO: handle the submission ID and permissions
    */
    $review = json_decode($_GET['reviews']);
    $handler = new PCRHandler();
    $stnid = $_SESSION['user_id'];
    $id = $_SESSION['id'];
    for ($i = 0; $i < count($review); $i++) {
        /* Id of reviewer, submission id, start position, start line, 
            end position, end line, comment contents, filename
        */
        echo $review[$i]['comment'] . "%%";
        $handler->getReview($stnid, $id, $review[$i]->startIndexSet, $review[$i]->startLine, $review[$i]->comment,
        $review[$i]->text, $review[$i]->reviewID, $review[$i]->fileName);
    }
?>
