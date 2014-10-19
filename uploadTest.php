<?php
require_once('includes/handlers.php');

$handler = new PCRHandler();
$handler->uploadTest($_POST["assignment_id"]);

header("Location: create.php?assid=".$_POST["assignment_id"]);
?>
