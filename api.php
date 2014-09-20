<?php

require_once('includes/handlers.php');
require_once('includes/db.php');
$backend = new PCRBackend();
echo $backend->handleRequest();

?>
