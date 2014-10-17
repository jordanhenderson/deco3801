<?php
require_once('includes/handlers.php');

$handler = new PCRHandler();
$handler->uploadArchive($_POST["assignment_id"]);

header("Location: overview.php?assid=".$_POST["assignment_id"]);
?>
