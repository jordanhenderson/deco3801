<?php
	require_once('includes/handlers.php');

	$handler = new PCRHandler();
	$handler->assignReviews($_REQUEST["assid"]);

	header("Location: overview.php?assid=".$_REQUEST["assid"]);
?>
