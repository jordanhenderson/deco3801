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
	if($config['isadmin']) $_SESSION['admin'] = true;
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
	$h = floor(($s%86400)/3600);
	$d = floor($s/86400);
	$str = "";
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
	<?php echo "<!-- User ID: $_SESSION[user_id] -->\n"; include 'header.php'; ?>
	
	<div class="container">
		<h1>Peer Code Review Home Page</h1>
		<div class="col-lg-12">
			<h2>Assignments</h2>
			<?php
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
						
						$SubmitTime = 0;
						if (!$admin) { // student
							$sub = $crs->getSubmission($asg['AssignmentID']);
							if ($sub->isValid()) {
								$sub = &$sub->getRow();
								$date = date_create_from_format('Y-m-d G:i:s', $sub['SubmitTime']);
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
						
						echo '
						<td>'.formatDBtime($asg['OpenTime']).'</td>';
						
						echo '
						<td>'.formatDBtime($asg['DueTime']).'</td>';
						
						echo '
						<td>'.formatDBtime($asg['ReviewsDue']).'</td>';
						
						echo "
						<td>$asg[Weight]%</td>
						<td>";
						
						// Status
						if ($SubmitTime == 0 && $CurrentTime < $OpenTime) { // Not Open
							echo 'Not open for submission.<br><i>Submissions open in: '.seconds2human($timeUntilOpen).'</i>';
						} else if ($admin) {
							if ($CurrentTime < $DueTime) {
								echo 'Submissions open.<br><i>Submissions close in: '.seconds2human($timeUntilDue).'</i>'; // Open
							} else if ($CurrentTime < $ReviewsDue) {
								echo 'Submissions closed.<br><i>Reviews close in: '.seconds2human($timeUntilReview).'</i>'; // Closed
							} else {
								echo 'Submissions closed.<br>Reviews closed.'; // Closed
							}
						} else if ($SubmitTime == 0 && $CurrentTime < $DueTime) { // Not Submitted
							echo 'Not submitted.<br><i>Submissions close in: '.seconds2human($timeUntilDue).'</i>';
						} else if ($SubmitTime == 0) { // Overdue
							echo 'Overdue.';
						} else if ($SubmitTime <= $DueTime) { // Submitted on time
							echo 'Submitted.';
						} else { // Submitted late (Shouldn't be possible)
							echo 'Submitted late.';
						}
						
						if ($admin || $CurrentTime < $DueTime) {
							// Nothing to see here.
						} else if ($CurrentTime <= $DueTime) { // Peer review not open
							echo '<br>Peer reviews not open.';
						} else if (count($reviewsTodo) == 0) { // Peer review complete
							echo '<br>Peer reviews complete.';
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

		</div><?php if (!$admin) { // Student only ?>
		<div class="col-md-6">
			<h2>Code Review and Feedback</h2>
			<p>All of your assigned submissions and feedback can be found here. Please take the time to assist your peers by offering suggestions and improvements as well as check over the advice your peers have offered you.</p>
			<p><a class="btn btn-warning" href="reviewhub.php" role="button">Start Now &raquo;</a></p>
		</div>
		<div class="col-md-6">
			<h2>Help Centre</h2>
			<p>If you would like to further assist students, please consider stopping by the Help Centre. Here you can find questions by students about assignments and you can even post a question of your own for help.</p>
			<p><a class="btn btn-info" href="help.php" role="button">Help Center &raquo;</a></p>
		</div><?php } ?>
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