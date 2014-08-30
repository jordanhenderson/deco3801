<?php

require_once('includes/handlers.php');

$backend = new PCRBackend();
echo $backend->handleRequest();
