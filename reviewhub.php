<?php
require_once 'includes/handlers.php';
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
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<h1>Review Hub</h1>
		<div class="col-lg-12">
			<h2>Assignments to Review</h2>
<?php
				$unmarkedSubmissions = array();
				$assignments = $crs->getCourse()->getAssignments();
				foreach ($assignments as $asg) {
					if (!$asg->isValid()) {
						continue;
					}
					array_merge($unmarkedSubmissions, $asg->getUnmarkedSubmissions());
				}
				
				if (empty($unmarkedSubmissions)) { // No submissions to mark
					echo "All of the assignments designated to you have been reviewed. Consider stopping by the Help Center to answer some of your peers' questions.";
				} else {
					// print table head
					echo '
					<table class="table">
						<thead>
							<tr>
								<th>Assignment Name</th>
								<th>Due Date</th>
								<th></th>
							</tr>
						</thead>
						<tbody>';
							// print table contents
							foreach ($unmarkedSubmissions as $sub) {
								$sub = &$sub->getRow();
								$asg = new Assignment(array("AssignmentID" => $sub['AssignmentID']));
								$asg = &$asg->getRow();
								echo "
							<tr>
								<td>$asg[AssignmentName]</td>
								<td>$asg[ReviewsDue]</td>
								<td><a class='btn btn-xs btn-info' href='review.php?subid=$sub[SubmissionID]' role='button'>Mark</a></td>
							<tr>";
							}
						echo '
						</tbody>
					</table>';
				}
			?>
		</div>
		<div class="col-lg-12">
			<h2>Feedback On Assignments</h2>
<?php
				$markedSubmissions = array();
				$assignments = $crs->getCourse()->getAssignments();
				foreach ($assignments as $asg) {
					if (!$asg->isValid()) {
						continue;
					}
					array_merge($markedSubmissions, $asg->getMarkedSubmissions());
				}
				
				if (empty($markedSubmissions)) { // No submissions recieved
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
							foreach ($markedSubmissions as $sub) {
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
	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>
	
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>