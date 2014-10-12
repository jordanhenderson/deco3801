<?php

require_once 'includes/handlers.php';

if (isset($_SESSION['admin']) && $_SESSION['admin']) {
	$admin = true;
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

function formatDBtime($dbtime) {
	$date = date_create_from_format('Y-m-d G:i:s', $dbtime);
	return date_format($date, 'j M \'y, g:ia'); // e.g: 6 Feb '14, 8:30pm
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
				<table class="table">
					<thead>
						<tr>
							<th>Assignment Name</th>
							<th>Course</th>
							<th>Weight</th>
							<th>Reviews/Student</th>
							<th>Open Date</th>
							<th>Due Date</th>
							<th>Reviews Due</th>
							<th>Test Results</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
							echo "<td>$asg[AssignmentName]</td>";
							echo "
							<td>$_SESSION[course_code]</td>";
							echo "
							<td>$asg[Weight]%</td>";
							echo "
							<td>$asg[ReviewsNeeded]</td>";
							echo '
							<td>'.formatDBtime($asg['OpenTime']).'</td>';
							echo '
							<td>'.formatDBtime($asg['DueTime']).'</td>';
							echo '
							<td>'.formatDBtime($asg['ReviewsDue']).'</td>';

							echo "
							<td>[results]</td>";
							?>

						</tr>
					</tbody>
				</table>
				<?php
					if ($admin) {
						echo '<a class="btn btn-primary" href="create.php?assid='.$_REQUEST['assid'].'" role="button">Edit Assignment</a>';
					}
				?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h2>Submissions</h2>
				<?php
				
				if ($admin) { // ADMIN
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
							<td>TODO: $sub[StudentID]</td>
							<td>$sub[Results]</td>
							<td>$sub[SubmitTime]</td>
							<td>";
							
							if (empty($submissions)) { // No submissions
								echo "No reviews.";
							} else {
								foreach ($reviews as $rev) {
									if (!$rev->isValid()) {
										continue;
									}
									$rev = &$rev->getRow();
									echo "<a href=\"#\">$rev[Comments] - $rev[text]</a><br>";
								}
							}
							echo '</td>
						</tr>';
						}
						echo '
					</tbody>
				</table>';
					}
				} else { // STUDENT
					
				}
				?>

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