<?php

session_start();

//Forcefully pull new changes when visiting index.php
exec("git pull && git reset --hard origin/master");

require_once 'includes/handlers.php';
require_once 'blti/blti.php'; // Load up the Basic LTI Support code
// Initialize: set secret, do not set session, and do not redirect
$context = new BLTI('oF0jxF1IGjzxYUl9w8B', false, false);

$crs = new PCRHandler(); // new PCRHandler for session

if ($context->valid) { // Redirect from Moodle, reload data, in case different course.
	session_unset(); // clear old data, ready for reload from POST
	$_SESSION['user_id'] = $_POST['user_id'];
	$_SESSION['userfullname'] = $_POST['lis_person_name_full'];
	$_SESSION['course_id'] = $_POST['context_id'];
	$_SESSION['course_code'] = $_POST['context_label'];
	$_SESSION['course_title'] = $_POST['context_title'];
	$_SESSION['helpenabled'] = $crs->getCourse()->helpEnabled();
	if ($context->isInstructor()) {
		$_SESSION['admin'] = true;
	}
	
} else if (isset($_SESSION['user_id'])) {
	// No action, since user is already authenticated, and data stored
} else {
	header('Location: invalid.php');
	exit(); // User didn't come from Moodle, and isn't authenticated.
}

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
	$str = ""; //do i need this?
	if ($d) {
		$str .= "$d days, ";
	}
	if ($h) {
		$str .= "$h hours, ";
	}
	return "$str$m mins";
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
<?php /*
// DEBUG & INFO
echo '<pre style="height: 18pc; overflow-y: scroll;"><b>Context Information:</b>';
echo $context->dump();
echo "\n<b>POST Parameters:</b>\n";
foreach ($_POST as $key => $value) {
	echo "$key = $value\n";
}
echo "\n<b>Assignments</b>\n";
print_r(array_values($crs->getCourse()->getAssignments()));
echo "</pre>\n";
*/ ?>
		<div class="col-lg-12">
			<h2>Assignments</h2>
			<?php
				$assignments = $crs->getCourse()->getAssignments();
				if (is_null($assignments)) { // No assignments
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
						<th>Weight</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>';
					// print table contents
					foreach ($assignments as $asg) {
						$asg = $asg->jsonSerialize();
						
						// Convert and store the dates from the DB as Unix timestamps.
						$CurrentTime = time();
						$date = date_create_from_format('Y-m-d G:i:s', $asg['OpenTime']);
						$OpenTime = (int) date_format($date, 'U');
						
						$date = date_create_from_format('Y-m-d G:i:s', $asg['DueTime']);
						$DueTime = (int) date_format($date, 'U');
						
						if (!$admin) { // student
							$sub = $crs->getSubmission($asg['AssignmentID'])->jsonSerialize();
							$date = date_create_from_format('Y-m-d G:i:s', $sub['SubmitTime']);
							$SubmitTime = (int) date_format($date, 'U');
						}
						
						if ($admin && $CurrentTime < $OpenTime) { // Not open (Admin only)
							$total = $OpenTime - $CurrentTime;
							echo "
					<tr href=\"create.php?a=$asg[AssignmentID]\">
						<td>$asg[AssignmentName]<br><i>Not Open</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Opens in: ".seconds2human($total)."<br><i>Click to Edit</i></td>
					</tr>";
						} else if ($admin && $CurrentTime <= $DueTime) { // Currently open (Admin only)
							$total = $DueTime - $CurrentTime;
							echo "
					<tr href=\"create.php?a=$asg[AssignmentID]\">
						<td>$asg[AssignmentName]<br><i>Open</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Closes in: ".seconds2human($total)."<br><i>Click to Edit</i></td>
					</tr>";
						} else if ($admin && $CurrentTime > $DueTime) { // Currently closed (Admin only)
							$total = $CurrentTime - $DueTime;
							echo "
					<tr href=\"create.php?a=$asg[AssignmentID]\">
						<td>$asg[AssignmentName]<br><i>Closed</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Closed ".seconds2human($total)." ago<br><i>Click to Edit</i></td>
					</tr>";
						} else if ($CurrentTime < $OpenTime) { // Not open (Student only)
							$total = $OpenTime - $CurrentTime;
							echo "
					<tr>
						<td>$asg[AssignmentName]<br><i>Not Open For Submission</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Not Open. Opens in: ".seconds2human($total)."</td>
					</tr>";
						} else if ($SubmitTime == 0 && $CurrentTime > $DueTime) { // Currently overdue (Student only)
							$total = $CurrentTime - $DueTime;
							echo "
					<tr class=\"bg-danger\">
						<td>$asg[AssignmentName]<br><i>Overdue</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Not Submitted. Due: $asg[DueTime]<br><i>Late by: ".seconds2human($total)."</i></td>
					</tr>";
						} else if ($SubmitTime == 0) { // Not submitted, still open (Student only)
							$total = $DueTime - $CurrentTime;
							echo "
					<tr class=\"bg-warning\">
						<td>$asg[AssignmentName]<br><i>Not Submitted</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Not Submitted. Due: $asg[DueTime]<br><i>Remaining: ".seconds2human($total)."</i></td>
					</tr>";
						} else if ($SubmitTime > $DueTime) { // Submitted overdue (Student only)
							$total = $SubmitTime - $DueTime;
							echo "
					<tr class=\"bg-success\">
						<td>$asg[AssignmentName]<br><i>Submitted Overdue</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Submitted: $sub[SubmitTime]<br><i>Late by: ".seconds2human($total)."</i></td>
					</tr>";
						} else if ($SubmitTime <= $DueTime) { // Submitted on time (Student only)
							echo "
					<tr class=\"bg-success\">
						<td>$asg[AssignmentName]<br><i>Submitted</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Submitted: $sub[SubmitTime]</td>
					</tr>";
						} else {
							echo "error...<br>\n";
						}
					}
					// print table end
					echo "
				</tbody>
			</table>";
				}
				// admin's have a button to create new assignments
				if ($admin) {
					echo '
			<a class="btn btn-primary" href="create.php" role="button">Create New Assignment</a>';
				}
			?>

		</div>
		<div class="col-md-6">
			<h2>Code Review</h2>
			<?php
			if ($admin) {
				// teacher still has the option to review submissions. Count ALL submissions.
				echo '<p>There are currently '.'3'.' student submitted assignments that have not recieved a teacher review. If no teacher input is required, then these can be dismissed at any time, either individually or per assignment.</p>
			<p><a class="btn btn-info" href="reviewhub.php" role="button">Review Assignments &raquo;</a></p>';
			} else if (mt_rand(0, 1)) { // reviews need marking
				//TODO ^ Actually decide this at some point
				echo '<p>There are '.'3'.' submissions ready for reviewing. Please take the time to assist your peers by offering suggestions and improvements.</p>
			<p><a class="btn btn-warning" href="reviewhub.php" role="button">Start Now &raquo;</a></p>';
			} else { // no reviews to mark
				echo '<p>All your assigned submissions to date have already been reviewed. However, if you would like to further assist your peers, consider stopping by the Help Center to answer some of your peers\' questions.</p>
			<p><a class="btn btn-info" href="help.php" role="button">Help Center &raquo;</a></p>';
			}
			?>

		</div>
		<div class="col-md-6">
			<h2>Feedback</h2>
			<?php
			if ($admin) {
				// teacher still gets to see feedback. Count ALL feedback.
				echo '<p>There are currently '.'3'.' pieces of student submitted feedback that have not recieved a teacher review. If no teacher input is required, then these can be dismissed at any time, either individually or per assignment.</p>
			<p><a class="btn btn-info" href="reviewhub.php" role="button">Review Feedback &raquo;</a></p>';
			} else if (mt_rand(0, 1)) { // feedback received
				// TODO ^ Actually decide this at some point
				echo '<p>You have recieved feedback from your assignment submission. Please take the time to check over the advice offered by your peers.</p>
			<p><a class="btn btn-success" href="reviewhub.php" role="button">Check it out &raquo;</a></p>';
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