<?php

session_start();

require_once 'config.php';

if ($config['DEBUG'] == false) {
	//Forcefully pull new changes when visiting index.php
	exec("git pull && git reset --hard origin/master");
} else {
	session_unset();
	$_SESSION['user_id'] = "0";
	$_SESSION['userfullname'] = "Debug User";
	$_SESSION['course_id'] = "0";
	$_SESSION['course_code'] = "Debug Course";
	$_SESSION['course_title'] = "Debug Course";
	$_SESSION['helpenabled'] = "1";
	$_SESSION['admin'] = true;
}

/* LTI Handling */
require_once 'blti/blti.php'; // Load up the Basic LTI Support code
// Initialize: set secret, do not set session, and do not redirect
$context = new BLTI($config['blti_psk'], false, false);

if ($context->valid) { // Redirect from Moodle, reload data, in case different course.
	session_unset(); // clear old data, ready for reload from POST
	$_SESSION['user_id'] = $_POST['user_id'];
	$_SESSION['userfullname'] = $_POST['lis_person_name_full'];
	$_SESSION['course_id'] = $_POST['context_id'];
	$_SESSION['course_code'] = $_POST['context_label'];
	$_SESSION['course_title'] = $_POST['context_title'];
	if ($context->isInstructor()) {
		$_SESSION['admin'] = true;
	}
} else if (isset($_SESSION['user_id'])) {
	// No action, since user is already authenticated, and data stored
} else {
	header('Location: invalid.php');
	exit(); // User didn't come from Moodle, and isn't authenticated.
}

require_once 'includes/handlers.php';
$crs = new PCRHandler();

$_SESSION['helpenabled'] = $crs->getCourse()->helpEnabled();

// Pull admin from session var to local var for easier/faster calling
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
	$admin = true;
} else {
	$admin = false;
}

function seconds2human($s) {
	$m = floor(($s%3600)/60);
	//$m = round(($ss%3600)/60, 0.1);
	$h = floor(($s%86400)/3600);
	$d = floor($s/86400);
	if ($d) {
		$str .= "$d days, ";
	}
	if ($h) {
		$str .= "$h hours, ";
	}
	return "$str$m mins";
}

function formatDBtime($dbtime) {
	$date = date_create_from_format('Y-m-d G:i:s', $dbtime);
	return date_format($date, 'j M Y, g:ia'); // e.g: 6 Feb 2014, 8:30pm
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Dashboard - PCR</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">

	<style>
		tbody > tr {
			cursor: pointer;
		}
	</style>
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<h1>Peer Code Review Home Page</h1>
		<div class="col-lg-12">
			<h2>Assignments</h2>
			<?php
				$unmarkedSubs = array();
				
				$assignments = $crs->getCourse()->getAssignments();
				if (empty($assignments)) { // No assignments
					echo "Currently no assignments have been released.";
				} else {
					// print table head
					echo '
			<table class="table">
				<thead>
					<tr>
						<th>Title</th>
						<th>Open Date</th>
						<th>Due Date</th>
						<th>Peer Review Due Date</th>
						<th>Weight</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>';
					// print table contents
					foreach ($assignments as $asg_obj) {
						if (!$asg_obj->isValid()) {
							continue;
						}
						
						// Both variables used later.
						$reviewsTodo = $asg_obj->getUnmarkedSubmissions($_SESSION['user_id']);
						$unmarkedSubs = array_merge($unmarkedSubs, $reviewsTodo);
						
						$asg = &$asg_obj->getRow();
						
						// Convert and store the dates from the DB as Unix timestamps.
						$CurrentTime = time();
						$date = date_create_from_format('Y-m-d G:i:s', $asg['OpenTime']);
						$OpenTime = (int) date_format($date, 'U');
						
						$date = date_create_from_format('Y-m-d G:i:s', $asg['DueTime']);
						$DueTime = (int) date_format($date, 'U');
						
						$date = date_create_from_format('Y-m-d G:i:s', $asg['ReviewsDue']);
						$ReviewsDue = (int) date_format($date, 'U');
						
						// Check if assignment needs to have reviews distributed.
						if (!$asg['ReviewsAllocated'] && $CurrentTime > $DueTime) {
							// Reviews have not been distributed to students. Do so now.
							include 'assignReviews.php';
							$asg_obj->setReviewsAllocated();
						}
						
						if (!$admin) { // student
							$sub = $crs->getSubmission($asg['AssignmentID']);
							if ($sub->isValid()) {
								$subRow = &$sub->getRow();
								$date = date_create_from_format('Y-m-d G:i:s', $subRow['SubmitTime']);
								$SubmitTime = (int) date_format($date, 'U');
							}
						}
						
						$timeUntilOpen = $OpenTime - $CurrentTime; // Opens in:
						$timeSinceOpen = $CurrentTime - $OpenTime; // Opened:ago
						$timeUntilDue = $DueTime - $CurrentTime; // Due in:
						$timeSinceDue = $CurrentTime - $DueTime; // Closed:ago
						$timeUntilReview = $ReviewsDue - $CurrentTime; // Due in:
						$timeSinceReview = $CurrentTime - $ReviewsDue; // Closed:ago
						
						echo "
					<tr href=\"overview.php?assid=$asg[AssignmentID]\">
						<td>$asg[AssignmentName]</td>";
						
						if ($CurrentTime < $OpenTime) { // Before open time
							echo '
						<td>'.formatDBtime($asg['OpenTime']).'<br><i>Opens in: '.seconds2human($timeUntilOpen).'</i></td>';
						} else { // After open time
							echo '
						<td>'.formatDBtime($asg['OpenTime']).'<br><i>Opened: '.seconds2human($timeSinceOpen).' ago</i></td>';
						}
						
						if ($CurrentTime <= $DueTime) { // Before due time
							echo '
						<td>'.formatDBtime($asg['DueTime']).'<br><i>Due in: '.seconds2human($timeUntilDue).'</i></td>';
						} else { // After due time
							echo '
						<td>'.formatDBtime($asg['DueTime']).'<br><i>Closed: '.seconds2human($timeSinceDue).' ago</i></td>';
						}
						
						if ($CurrentTime <= $ReviewsDue) { // Before reviews due time
							echo '
						<td>'.formatDBtime($asg['ReviewsDue']).'<br><i>Due in: '.seconds2human($timeUntilReview).'</i></td>';
						} else { // After reviews due time
							echo '
						<td>'.formatDBtime($asg['ReviewsDue']).'<br><i>Closed: '.seconds2human($timeSinceReview).' ago</i></td>';
						}
						
						echo "
						<td>$asg[Weight]%</td>
						<td>";
						
						// Status
						if ($admin) {
							echo '-';
						} else if ($SubmitTime == 0 && $CurrentTime < $DueTime) { // Not Submitted
							echo 'Not Submitted.';
						} else if ($SubmitTime == 0) { // Overdue
							echo 'Overdue.';
						} else if ($SubmitTime <= $DueTime) { // Submitted on time
							echo 'Submitted.';
						} else { // Submitted late
							echo 'Submitted late.';
						}
						
						if ($admin) {
							// Nothing to see here.
						} else if ($CurrentTime <= $DueTime) { // Peer review not open
							echo '<br>Peer Reviews Not Open.';
						} else if (count($reviewsTodo) == 0) { // Peer review complete
							echo '<br>Peer Reviews Complete.';
						} else { // Peer review incomplete
							echo '<br>'.count($reviewsTodo).' Peer Reviews Incomplete.';
						}
						
						echo '</td>
					</tr>';
					}
					echo '
				</tbody>
			</table>';
				}
				// admin's have a button to create new assignments
				if ($admin) {
					echo '
			<a class="btn btn-primary" href="create.php" role="button">Create New Assignment</a>';
				}
			?>

		</div>
		<div class="col-md-6">
			<h2>Code Review and Feedback</h2>
			<?php
			/**
			*
			* I think this should be tidied up a bit. Since people can see everything on the review hub page maybe even delete if blocks and
			* put 'All of your assigned submissions and feedback may be found here', then on the review hub page if there are no
			* reviews assigned then put message suggesting to go to the help center
			*/
			if ($admin) {
				// teacher still has the option to review submissions. Count ALL submissions.
				// teacher still gets to see feedback. Count ALL feedback.
				echo '<p>There are currently '.'TODO'.' student submitted assignments that have not recieved a teacher review. If no teacher input is required, then these can be dismissed at any time, either individually or per assignment.</p>
			<p>There are currently '.'3'.' pieces of student submitted feedback that have not recieved a teacher review. If no teacher input is required, then these can be dismissed at any time, either individually or per assignment.</p>
			<p><a class="btn btn-info" href="reviewhub.php" role="button">Review Assignments &raquo;</a></p>';
			} else if (count($unmarkedSubs) > 0) { // reviews need marking
				echo '<p>There are '.count($unmarkedSubs).' submissions ready for reviewing. Please take the time to assist your peers by offering suggestions and improvements.</p>
			<p><a class="btn btn-warning" href="reviewhub.php" role="button">Start Now &raquo;</a></p>';
			} else { // no reviews to mark
				echo '<p>All your assigned submissions to date have already been reviewed. However, if you would like to further assist your peers, consider stopping by the Help Center to answer some of your peers\' questions.</p>
			<p><a class="btn btn-info" href="help.php" role="button">Help Center &raquo;</a></p>';
			}
			
			if (mt_rand(0, 1)) { // feedback received
				// TODO ^ Actually decide this at some point
				echo '<p>You have recieved feedback from your assignment submission. Please take the time to check over the advice offered by your peers.</p>';
				//Don't want to print the button if it already exists
				if(count($unmarkedSubs) = 0) {
					'<p><a class="btn btn-success" href="reviewhub.php" role="button">Check it out &raquo;</a></p>';
				}
			} else { // no feedback
				echo '<p>You have already viewed the feedback from all of your assignment submissions. Consider checking it over again to make the most of the advice.</p>
			<p><a class="btn btn-info" href="reviewhub.php" role="button">Check it out &raquo;</a></p>';
			}
			?>

		</div>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	
	<!-- Assignments Table -->
	<script type="text/javascript">
		$('tr').on("click", function() {
			if ($(this).attr('href') !== undefined) {
				document.location = $(this).attr('href');
			}
		});

		$('tbody > tr').hover(
			function() {
				$(this).addClass("bg-info");
			}, function() {
				$(this).removeClass("bg-info");
			}
		);
	</script>
</body>
</html>
