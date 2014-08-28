<?php

include('includes/db.php');
include('includes/handlers.php');

$backend = new PCRBackend();
echo $backend->handleRequest();
