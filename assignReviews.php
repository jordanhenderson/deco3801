<?php
	require_once('includes/handlers.php');
	
	$handler = new PCRHandler();
	$handler->assignReviews($_REQUEST["assid"]);
	
	// Redirect to overview page (only)
	header("Location: overview.php?assid=".$_REQUEST["assid"]);
?>
