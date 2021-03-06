<?php

require_once 'includes/handlers.php';

if (isset($_SESSION['admin']) && $_SESSION['admin']) {
	$admin = true;
} else {
	$admin = false;
}

$crs = new PCRHandler();

if (isset($_REQUEST['assid'])) {
	$assid = $_REQUEST['assid'];
	$assignment = $crs->getAssignment($assid);
	
	if ($assignment->isValid()) {
		$asg = &$assignment->getRow();
		if ($asg['CourseID'] != $_SESSION['course_id']) {
			exit("Assignment is for a different course (course_id = $asg[CourseID]). Please log in to that course's page from Moodle to access it.");
		}
	} else {
		exit("Corrupt/Invalid assignment. Please contact site administrator, with code: \"assid=$assid)\"");
	}
} else {
	exit("No Assignment Specified For Overview.");
}

function formatDBtime($dbtime) {
	$date = date_create_from_format('Y-m-d G:i:s', $dbtime);
	return date_format($date, 'j M \'y, g:ia'); // e.g: 6 Feb '14, 8:30pm
}

function printResults($results) {
	global $asg;
	

	$results = json_decode($results);
	
	global $assignment;
	$percentage = $passed = $numTests = 0;
	if ($assignment->isValid()) {
	    $numTests = $asg["NumberTests"];
	    if ((int)$numTests > 0) {
		$passed = 0;
		if ($results) {
			foreach($results as $val) {
			    if($val == 'pass') $passed++;
			}
		}
		
		$percentage = ($passed/$numTests)*100;
	    }
	}

	echo "$passed/$numTests tests passed";
	echo "
    <div style='width:100%; background-color:white; height:auto; border:1px solid #000;'>
    	<div style='width:".$percentage."%; background-color:green; height:10px;'></div>
	</div>";
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
				<li><a href="#">Assignment Overview</a><span class="divider"></span></li>
			</ul>
		</div> 
		<h1><?php echo $asg['AssignmentName']; ?> Overview</h1>
		<div class="row">
			<div class="col-md-12">
				<h2>Information</h2>
				<table class="table">
					<thead>
						<tr>
							<th>Assignment Name</th>
							<th>Weight</th>
							<th>Reviews/Student</th>
							<th>Open Date</th>
							<th>Due Date</th>
							<th>Reviews Due</th>
							<?php if (!$admin) {
							echo '
							<th>Test Results</th>';
							} ?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
							echo "<td>$asg[AssignmentName]</td>";
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
							
							if (!$admin) {
								echo '<td>';
								$sub = $crs->getSubmission($assid);
								$results = "";
								if($sub->isValid()) {
								    $results = $sub->getRow()["Results"];
								}
								printResults($results);
								echo '</td>';
							}
							?>

						</tr>
					</tbody>
				</table>
				<?php
					if ($admin) {
						echo '<a class="btn btn-primary" href="create.php?assid='.$assid.'" role="button">Edit Assignment</a><br>';
						
						$currentTime = new DateTime();
						$date = date_create_from_format('Y-m-d G:i:s', $asg['DueTime']);
						if($date <= $currentTime) {
							echo '<a class="btn btn-warning" href="assignReviews.php?assid='.$assid.'" role="button">Assign Reviews</a><br>';
						}
						echo '<h3>Quick Actions</h3>';
						echo '<a class="btn btn-danger impatient" name="makeopen" role="button">Make Assignment Open</a><br>';
						echo '<a class="btn btn-danger impatient" name="makedue" role="button">Make Assignment Due</a><br>';
						echo '<a class="btn btn-danger impatient" name="makereviewsdue" role="button">Make Assignment Reviews Due</a><br>';
					}
					
				?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
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
							// getStudentsReviews at some point? Might be difficult.
							// This way we won't flood the screen with 500 comments
							// we'll just have 1 link for every student who made a
							// review.
							$reviews = $sub->getReviews();
							$sub = &$sub->getRow();
							
							echo "
						<tr>
							<td>Student #$sub[StudentID]</td>
							<td>";
							printResults($sub["Results"]);
							echo "</td>
							<td>$sub[SubmitTime]</td>
							<td>";
							
							if (empty($reviews)) { // No reviews
								echo "No reviews.";
							} else {
								foreach ($reviews as $rev) {
									if (!$rev->isValid() || $rev->getRow()['FileID'] == '') {
										continue;
									}
									$rev = &$rev->getRow();
									echo '<a href="review.php?subid=' . $rev['SubmissionID'] . '" style="font-family: Monospace">'.str_replace(array(' ', '\n'), '&nbsp;', str_pad(substr($rev['text'], 0, 33), 33)).' - "'.substr($rev['Comments'], 0, 33).' ..."</a><br>';
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
					// Get times
					$CurrentTime = time();
					$date = date_create_from_format('Y-m-d G:i:s', $asg['OpenTime']);
					$OpenTime = (int) date_format($date, 'U');
					$date = date_create_from_format('Y-m-d G:i:s', $asg['DueTime']);
					$DueTime = (int) date_format($date, 'U');
					
					$submission = $assignment->getSubmission($_SESSION['user_id']);
					if ($submission->isValid()) {
						$srow = $submission->getRow();
						echo "<span>Your last submission was made on: $srow[SubmitTime]</span>";
					} else if ($CurrentTime < $OpenTime) {
						echo '<span>This assignment is not yet open for submission.';
					} else if ($CurrentTime > $DueTime) {
						echo '<span>No submissions may be made past the due date.';
					} else {
						echo '<span>You have not yet made a submission for this assignment.';
						if (!$assignment->canResubmit()) {
							echo '<br>You may <strong>not</strong> make multiple submissions on this assignment - please ensure your assignment is correct before attempting to submit.';
						} else {
							echo '<br>You may make multiple submissions.';
						}
					}
					echo '</span>';
					if ($CurrentTime <= $DueTime && $CurrentTime >= $OpenTime &&
						($submission->isValid() && $assignment->canResubmit() || !$submission->isValid())) {
					?>
				<br>
				<a href="submit.php?assid=<?php echo $assid; ?>">
					<span class="btn btn-default btn-primary">New Submission</span>
				</a>
					<?php
					}
				}
				?>

			</div>
		</div>
	</div>
	
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	
	<!-- Bootstrap datetimepicker JavaScript -->
	<script src="js/bootstrap-datetimepicker.min.js"></script>
	<script>
		$(document).ready(function() {
			$("#breadcrumbs").rcrumbs();
		});
		
		$(".impatient").click(function() {
			var func = $(this).attr("name");
			var funcparams = [<?php echo '"'.$assid.'"'; ?>];
			var request = {f: func, params: funcparams};
			$.post("api.php", JSON.stringify(request), function() {});
			alert("Done.");
		});
	</script>
</body>
</html>