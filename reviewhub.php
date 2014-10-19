<?php

require_once 'includes/handlers.php';

if (isset($_SESSION['admin']) && $_SESSION['admin']) {
	exit(); // Admin has no reason to use this page.
}

$crs = new PCRHandler();

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>Review Hub - PCR</title>
	
	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
	
	<!-- Breadcrumbs -->
	<!-- jQuery -->
	<link rel="stylesheet" type="text/css" href="css/jquery.rcrumbs.css">
	<script src="js/jquery-1.11.0.js"></script>
	<script src="js/jquery.rcrumbs.js"></script>
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<div class="rcrumbs" id="breadcrumbs">
			<ul>
				<li><a href="http://deco3801-14.uqcloud.net">Home</a><span class="divider">></span></li>
				<li><a href="#">Review Hub</a><span class="divider"></span></li>
			</ul>
		</div>
		<h1>Review Hub</h1>
		<div class="col-lg-12">
		<?php
		
		$assignments = $crs->getCourse()->getAssignments();
		
		echo '<h2>Assignments to Review</h2>';
		$unmarkedSubs = array();
		foreach ($assignments as $asg) {
			if (!$asg->isValid()) {
				continue;
			}
			$unmarkedSubs = array_merge($unmarkedSubs, $asg->getUnmarkedSubmissions($_SESSION['user_id']));
		}
		
		if (empty($unmarkedSubs)) { // No submissions to mark
			echo '
			All of the assignments designated to you have been reviewed. Consider stopping by the Help Center to answer some of your peers\' questions.';
		} else {
			// print table head
			echo '
			<table class="table">
				<thead>
					<tr>
						<th>Assignment Name</th>
						<th>Student ID #</th>
						<th>Due Date</th>
						<th></th>
					</tr>
				</thead>
				<tbody>';
			// print table contents
			foreach ($unmarkedSubs as $sub) {
				$sub = &$sub->getRow();
				$asg = new Assignment(array("AssignmentID" => $sub['AssignmentID']));
				$asg = &$asg->getRow();
				echo "
					<tr>
						<td>$asg[AssignmentName]</td>
						<td>$sub[StudentID]</td>
						<td>$asg[ReviewsDue]</td>
						<td><a class='btn btn-xs btn-info' href='review.php?subid=$sub[SubmissionID]' role='button'>Mark</a></td>
					<tr>";
			}
			echo '
				</tbody>
			</table>';
		}
		echo '
		</div>
		<div class="col-lg-12">
			<h2>Assignments with Feedback</h2>';
		$markedSubs = array();
		$assignments = $crs->getCourse()->getAssignments();
		foreach ($assignments as $asg) {
			if (!$asg->isValid()) {
				continue;
			}
			$markedSubs = array_merge($markedSubs, $asg->getMarkedSubmissions($_SESSION['user_id']));
		}
		
		if (empty($markedSubs)) { // No submissions recieved
			echo "There is currently no feedback available for any of your submissions.";
		} else {
			// print table head
			echo '
			<table class="table">
				<thead>
					<tr>
						<th>Assignment Name</th>
						<th></th>
					</tr>
				</thead>
				<tbody>';
			// print table contents
			foreach ($markedSubs as $sub) {
				$sub = &$sub->getRow();
				$asg = new Assignment(array("AssignmentID" => $sub['AssignmentID']));
				$asg = &$asg->getRow();
				echo "
					<tr>
						<td>$asg[AssignmentName]</td>
						<td><a class='btn btn-xs btn-info' href='review.php?subid=$sub[SubmissionID]' role='button'>View</a></td>
					<tr>";
			}
			echo '
				</tbody>
			</table>';
		}
		?>
		</div>
	
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	
	<script>
		$(document).ready(function() {
			$("#breadcrumbs").rcrumbs();
		});
	</script>
</body>
</html>