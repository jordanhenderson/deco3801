<?php

session_start();

require_once 'includes/handlers.php';
//require_once 'includes/db.php'; // Don't think we need this, since already included in handlers.php

// Load up the Basic LTI Support code
require_once 'blti/blti.php';

// Initialize: set secret, do not set session, and do not redirect
$context = new BLTI('oF0jxF1IGjzxYUl9w8B', false, false);

if ($context->valid) { // New redirect from Moodle. Probably different course.
	session_unset(); // clear old data, ready for reload from POST
	$_SESSION['user_id'] = $_POST['user_id'];
	$_SESSION['course_id'] = $_POST['context_id'];
	$_SESSION['course_code'] = $_POST['context_label'];
	$_SESSION['course_title'] = $_POST['context_title'];
	helpEnabled($_SESSION['course_id']);
	$crs = new PCRHandler();
	$crs->getCourse();
	echo "<!-- New login -->\n";
} else if (isset($_SESSION['user_id'])) {
	; // No action, since user is already authenticated.
	echo "<!-- Already logged in -->\n";
} else {
	//header('Location: invalid.php');
	//exit(); // User didn't come from Moodle, and isn't authenticated.
	echo "<!-- Not logged in -->\n";
}

//IM leaving this here for now but i'll relocate it to the db.php when i stop being bad
function helpEnabled($courseID) {
	$con = mysqli_connect("localhost","deco3801","hh2z2WG2q","deco3801") or die("Error: ".mysqli_error($con));
	$sql = "SELECT HelpEnabled FROM `Course` WHERE CourseID=$courseID";
	$query = mysqli_query($con, $sql);

	while($row = mysqli_fetch_array($query)){	
		$help = $row['HelpEnabled'];
	}
	$_SESSION['helpenabled'] = $help;
	mysqli_close($con);
	return $help;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
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
	<?php require 'header.php'; ?>
	
	<div class="container">
		<h1>Peer Code Review Home Page</h1>
<?php

print "<pre>\n<b>Context Information:</b>\n";
print $context->dump();
print "\n\n<b>POST Parameters:</b>\n\n";
foreach ($_POST as $key => $value) {
	print "$key = $value\n";
}
print "</pre>\n";
?>
		<div class="col-lg-12">
			<h2>Assignments</h2>
			<?php
				//$assignment = $crs->getAssignment();
				echo "<pre>";
				if (is_null($crs->getCourse()->getAssignments())) {
					echo "is null";
				} else {
					echo $crs->getCourse()->getAssignments();
					
					$assignments = $crs->getCourse()->getAssignments();
					print_r(array_values($assignments));
				}
				echo "</pre>";
			?>
			<table class="table">
				<thead>
					<tr>
						<th>Title</th>
						<th>Course</th>
						<th>Open Date</th>
						<th>Due Date</th>
						<th>Weight</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<tr class="submitted">
						<td>Assignment 1<br><span>Submitted</span></td>
						<td>CSSE1001</td>
						<td>5/9/14</td>
						<td>5/9/14</td>
						<td>10%</td>
						<td>Closed for submission</td>
					</tr>
					<tr class="unsubmitted">
						<td>Assignment 2<br><span>Not Submitted</span></td>
						<td>CSSE1001</td>
						<td>5/9/14</td>
						<td>5/9/14</td>
						<td>10%</td>
						<td>Open for submission</td>
					</tr>
					<tr class="unsubmitted">
						<td>Assignment 3<br><span>Not Submitted</span></td>
						<td>CSSE1001</td>
						<td>5/9/14</td>
						<td>5/9/14</td>
						<td>10%</td>
						<td>Closed for submission</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-6">
			<h2>Code Review</h2>
			<p>There are submissions ready for reviewing. Please take the time to assist your peers by offering suggestions and improvements.</p>
			<p><a class="btn btn-default" href="review.php" role="button">Start Now &raquo;</a></p>
		</div>
		<div class="col-md-6">
			<h2>Feedback</h2>
			<p>You have recieved feedback from your Assignment 1 submission. Please take the time to check over the advice offered by your peers.</p>
			<p><a class="btn btn-default" href="#" role="button">Check it out &raquo;</a></p>
		</div>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>
