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
		<h1>Review Hub</h1>
		<div class="col-lg-12">
			<h2>Assignments to Review</h2>
<?php
				$reviews = $crs->getStudent()->getReviews();

				if (empty($reviews)) { // No assignments
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
							foreach ($reviews as $rev) {
								$rev = &$rev->getRow();
								$submission = new Submission(array("SubmissionID"=>$rev["SubmissionID"]));
								$submission = &$submission->getRow();
								$Assignment = new Assignment(array("AssignmentID" => $submission['AssignmentID']));
								$Assignment = &$Assignment->getRow();
								echo "<tr>
								<td>$Assignment[AssignmentName]</td>
								<td>$Assignment[ReviewsDue]</td>
								<td><a class='btn btn-xs btn-info' href='review.php?subid=$submission[SubmissionID]' role='button'>Mark</a></td>
								<tr>";
							}
						echo '</tbody>
					</table>';
				}
			?>
		</div>
		<div class="col-lg-12">
			<h2>Feedback On Assignments</h2>
<?php
				$reviews = $crs->getStudent()->getFeedback();

				if (empty($reviews)) { // No assignments
					echo "There are no Reviews to display.";
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
							foreach ($reviews as $rev) {
								$rev = &$rev->getRow();						
								$submission = new Submission(array("SubmissionID"=>$rev["SubmissionID"]));
								$submission = &$submission->getRow();
								$Assignment = new Assignment(array("AssignmentID" => $submission['AssignmentID']));
								$Assignment = &$Assignment->getRow();
								echo "<tr>
								<td>$Assignment[AssignmentName]</td>
								<td><a class='btn btn-xs btn-info' href='review.php?subid=$submission[SubmissionID]' role='button'>View</a></td>
								<tr>";
							}
						echo '</tbody>
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
