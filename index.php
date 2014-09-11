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

if (isset($_SESSION['admin']) && $_SESSION['admin']) {
	$admin = true;
} else {
	$admin = false;
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

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
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
*/?>
		<div class="col-lg-12">
			<h2>Assignments</h2>
			<?php
				$assignments = $crs->getCourse()->getAssignments();
				if (is_null($assignments)) {
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
						$sub = $crs->getSubmission($asg['AssignmentID'])->jsonSerialize();
						$currentTime = time();
						
						$date = date_create_from_format('Y-M-d H:i:s', $sub['OpenTime']);
						$OpenTime = $date->getTimestamp();
						
						$date = date_create_from_format('Y-M-d H:i:s', $sub['DueTime']);
						$dueTime = $date->getTimestamp();
						
						$date = date_create_from_format('Y-M-d H:i:s', $sub['SubmitTime']);
						$submitTime = $date->getTimestamp();
						
						echo "submitTime: '".$submitTime."'\n";
						echo "dueTime: '".$dueTime."'\n";
						echo "currentTime: '".$currentTime."'\n\n";
						if ($submitTime == 0 && $dueTime < $currentTime) { // Overdue
							echo "
					<tr class=\"bg-danger\">
						<td>$asg[AssignmentName]<br><i>Overdue</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Not Submitted. Due: $asg[DueTime]<br>Overdue: X days, X hours, X mins</td>
					</tr>";
						} else if ($submitTime == 0) { // Not submitted
							echo "
					<tr class=\"bg-warning\">
						<td>$asg[AssignmentName]<br><i>Not Submitted</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Not Submitted. Due: $asg[DueTime]<br>Remaining: X days, X hours, X mins</td>
					</tr>";
						} else if ($submitTime > $dueTime) { // Submitted overdue
							echo "
					<tr class=\"bg-success\">
						<td>$asg[AssignmentName]<br><i>Submitted Overdue</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Submitted: $sub[SubmitTime]<br>Overdue: X days, X hours, X mins</td>
					</tr>"; // TODO ^
						} else { // Submitted
							echo "
					<tr class=\"bg-success\">
						<td>$asg[AssignmentName]<br><i>Submitted</i></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Submitted: $sub[SubmitTime]</td>
					</tr>";
						}
					}
					echo "
				</tbody>
			</table>";
				}
				if ($admin) {
					echo '<a class="btn btn-primary" href="create.php" role="button">Create New Assignment</a>';
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
			} else if (mt_rand(0, 1)) { //TODO Actually decide this at some point
				echo '<p>There are '.'3'.' submissions ready for reviewing. Please take the time to assist your peers by offering suggestions and improvements.</p>
			<p><a class="btn btn-warning" href="reviewhub.php" role="button">Start Now &raquo;</a></p>';
			} else {
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
			} else if (mt_rand(0, 1)) { //TODO Actually decide this at some point
				echo '<p>You have recieved feedback from your assignment submission. Please take the time to check over the advice offered by your peers.</p>
			<p><a class="btn btn-success" href="reviewhub.php" role="button">Check it out &raquo;</a></p>';
			} else {
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
</body>
</html>
