<?php

require_once 'includes/handlers.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
	exit("Not logged in as admin");
}

$crs = new PCRHandler();

if (isset($_REQUEST['assid'])) {
	$assignment = $crs->getAssignment($_REQUEST['assid']);
	if ($assignment->isValid()) {
		$asg = &$assignment->getRow();
		if ($asg['CourseID'] != $_SESSION['course_id']) {
			exit("Assignment is for a different course (course_id = $asg[CourseID]). Please log in to that course's page from Moodle to access it.");
		}
	} else {
		exit("Corrupt/Invalid assignment. Please contact site administrator, with code: \"assid=$_REQUEST[assid])\"");
	}
} else {
	exit("No Assignment Specified For Overview.");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title><?php echo $asg['AssignmentName']; ?> Overview - PCR</title>
	
	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">
	
	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<h1><?php echo $asg['AssignmentName']; ?> Overview</h1>
		<div class="row">
			<div class="col-md-12">
				<h2>Information</h2>
				<?php
				
				echo "Weight: ".$asg['Weight'];
				echo "<br>Reviews Needed: ".$asg['ReviewsNeeded'];
				echo "<br>Assignment Open: ".$asg['OpenTime'];
				echo "<br>Assignment Due: ".$asg['DueTime'];
				echo "<br>Reviews Due: ".$asg['ReviewsDue'];
				
				?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h2>Submissions</h2>
				<?php
				
				$submissions = $assignment->getSubmissions();
				
				if (empty($submissions)) { // No submissions
					echo "There are currently no submissions for this assignment.";
				} else {
					// print table head
					echo '
				<table class="table">
					<thead>
						<tr>
							<th>Student Name (ID)</th>
							<th>Results</th>
							<th>Submit Time</th>
							<th>Reviews</th>
						</tr>
					</thead>
					<tbody>';
				
					foreach ($submissions as $sub) {
						if (!$sub->isValid()) {
							continue;
						}
						$reviews = $sub->getReviews();
						$sub = &$sub->getRow();
						
						echo "
						<tr>
							<td>StudentID: $sub[StudentID]</td>
							<td>Results: $sub[Results]</td>
							<td>SubmitTime: $sub[SubmitTime]</td>
							<td>";
						
						if (empty($submissions)) { // No submissions
							echo "No reviews.";
						} else {
							foreach ($reviews as $rev) {
								if (!$rev->isValid()) {
									continue;
								}
								$rev = &$rev->getRow();
								echo "$rev[Comments] - $rev[text]";
							}
						}
						echo '</td>
						</tr>';
					}
					echo '
					</tbody>
				</table>';
				}
				
				?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h2>Something Else</h2>
			</div>
		</div>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>

	<!-- Bootstrap datetimepicker JavaScript -->
	<script src="js/bootstrap-datetimepicker.min.js"></script>
</body>
</html>