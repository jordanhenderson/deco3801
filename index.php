<?php

session_start();

require_once 'includes/handlers.php';
require_once 'blti/blti.php'; // Load up the Basic LTI Support code
// Initialize: set secret, do not set session, and do not redirect
$context = new BLTI('oF0jxF1IGjzxYUl9w8B', false, false);

if ($context->valid) { // Redirect from Moodle, reload data, in case different course.
	session_unset(); // clear old data, ready for reload from POST
	$_SESSION['user_id'] = $_POST['user_id'];
	$_SESSION['course_id'] = $_POST['context_id'];
	$_SESSION['course_code'] = $_POST['context_label'];
	$_SESSION['course_title'] = $_POST['context_title'];
	$crs = new PCRHandler();
	$var = $crs->getCourse()->helpEnabled();
	$_SESSION['helpenabled'] = $var;
	echo "<!-- New login -->\n";
} else if (isset($_SESSION['user_id'])) {
	// No action, since user is already authenticated, and data stored
	echo "<!-- Already logged in -->\n";
} else {
	header('Location: invalid.php');
	exit(); // User didn't come from Moodle, and isn't authenticated.
}


// @Kieran - I made another one of these in db.php for you.
// Should work, but if it doesn't, at least I tried

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
<?php
// DEBUG & INFO
echo '<pre style="height: 16pc; overflow-y: scroll;"><b>Context Information:</b>';
echo $context->dump();
echo "\n\n<b>POST Parameters:</b>\n";
foreach ($_POST as $key => $value) {
	echo "$key = $value\n";
}
echo "\n\n<b>Assignments</b>\n";
print_r(array_values($crs->getCourse()->getAssignments()));
echo "</pre>\n";
?>
		<div class="col-lg-12">
			<h2>Assignments</h2>
			<?php
				$assignments = $crs->getCourse()->getAssignments();
				if (is_null($assignments)) {
					echo "is null";
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
						$sub = $crs->getSubmission($asg['AssignmentID']);
						// Not sure if this^ works, since no submissions yet.
						echo "
					<tr>
						<td>$asg[AssignmentName]<br><span>Submitted</span></td>
						<td>$asg[OpenTime]</td>
						<td>$asg[DueTime]</td>
						<td>$asg[Weight]%</td>
						<td>Submitted: ".$sub->jsonSerialize()['SubmitTime']."</td>
					</tr>";
					}
					echo "
				</tbody>
			</table>";
				}
			?>
		</div>
		<div class="col-md-6">
			<h2>Code Review</h2>
			<?php
			if (mt_rand(0, 1)) { //TODO Actually decide this at some point
				echo '<p>There are '.'3'.' submissions ready for reviewing. Please take the time to assist your peers by offering suggestions and improvements.</p>
			<p><a class="btn btn-warning" href="reviewhub.php" role="button">Start Now &raquo;</a></p>';
			} else {
				echo '<p>All your assigned submissions to date have already been reviewed. However, if you would like to further assist your peers, consider stopping by the Help Center to answer some questions.</p>
			<p><a class="btn btn-info" href="help.php" role="button">Help Center &raquo;</a></p>';
			}
			
			?>
		</div>
		<div class="col-md-6">
			<h2>Feedback</h2>
			<?php
			if (mt_rand(0, 1)) { //TODO Actually decide this at some point
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
