<?php
    //Include the JWT functionality
    require_once ('JWT.php');

    /*
    Creates a consumer key and a secret for AnnotateIt --> the current 
    storage solution.
    This file will be deleted once the current AnnotateIt solution for
    storage is removed
    */
    $CONSUMER_KEY = "30d9cf1909d34daabff333acea9412c0";
    $CONSUMER_SECRET = "bd9f20ea-d0da-4252-900e-9cc262b11be2";
    // Expire in 1 day
    $CONSUMER_TTL = 86400;
    //Create an array with the AnnotateIt information
    $payload = array(
        'consumerKey' => $CONSUMER_KEY,
        'userId' => 'admin',
        'issuedAt' => time(),
        'ttl' => $CONSUMER_TTL
    );

    $jwt = new JWT();
    echo $jwt->encode($payload, $CONSUMER_SECRET);

?>