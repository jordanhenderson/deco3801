<?php

session_start();

require_once 'includes/handlers.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
	exit("Not logged in as admin");
}

$crs = new PCRHandler();

if (isset($_REQUEST['assid'])) { // TODO - restrict access to other courses assignments
	$asg = $crs->getAssignment($_REQUEST['assid']);
	if ($asg->isValid()) {
		$asg = $asg->getRow();
	} else {
		die();
	}
	$new = false;
} else {
	$new = true;
	$assignment = new PCRBuilder("Assignments");
	$asg = $assignment->getRow();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Create - PCR</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">
	
	<!-- Bootstrap datetimepicker CSS -->
	<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<?php
		if ($new) { 
			echo '
		<h1>Create New Assignment</h1>';
		} else {
			echo '
		<h1>Edit Existing Assignment</h1>';
		}
		?>
		<form role="form" >
			<div class="row">
				<div class="col-md-6">
					<label for="name">Assignment Name</label>
					<input class="form-control" type="text" id="name" <?php echo 'value="'.$asg['AssignmentName'].'"'; ?>></input>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-4">
					<label for="open">Open Date</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['OpenTime'].'"'; ?> class="form-control form_datetime" id="open">
					<p class="help-block">
						Date and time that assignment files are available, and submissions are permitted.
					</p>
				</div>
				<div class="col-md-4">
					<label for="due">Due Date</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['DueTime'].'"'; ?> class="form-control form_datetime" id="due">
					<p class="help-block">
						Date and time that the assignment must be submitted before.<br>
						A late submission will be declared to both the student and teacher.<br>
						The students will be allowed to begin peer reviewing <b>1 day</b> after this time. <br>
						A student who has not submitted before peer review begins can not participate in the peer review.<br>
						Students cannot submit after peer review begins.
					</p>
				</div>
				<div class="col-md-4">
					<label for="review-due">Peer Reviews Due</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['ReviewsDue'].'"'; ?> class="form-control form_datetime" id="review-due">
					<p class="help-block">
						Date and time that students must finish their reviews by.<br>
						Reviews are available from the due date onwards.
					</p>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="specfiles">Assignment Files</label>
						<input type="file" id="specfiles">
						<p class="help-block">PDF or zip containing the assignment specifications.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="testfiles">Unit Testing Files</label>
						<input type="file" id="testfiles">
						<p class="help-block">Please zip test file(s). The file executed when testing must be named "runtest"</p>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-4">
					<label for="weight">Weight (%)</label>
					<input <?php echo 'value="'.$asg['Weight'].'"'; ?> class="form-control" type="number" id="weight" min="1" max="100">
				</div>
				<div class="col-md-4">
					<label for="reviewnum">Reviews Per Student</label>
					<select class="selectpicker" data-width="120px" id="reviewnum">
						<option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10</option>
					</select>
					<p class="help-block">
						The amount of assignments each student will be assigned to review, after the due date.<br>
						Students who have not submitted before the deadline will not be able to create or recieve reviews.
					</p>
				</div>
			</div>
			<div align="center">
				<?php
				if ($new) {
					echo '
				<input class="btn btn-primary" role="button" type="submit" value="Create">';
				} else {
					echo '
				<input class="btn btn-primary" role="button" type="submit" value="Update">';
				}
				?>
				<input class="btn btn-warning" role="button" type="submit" value="Reset">
			</div>
		</form>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>

	<!-- Bootstrap datetimepicker JavaScript -->
	<script src="js/bootstrap-datetimepicker.min.js"></script>
	
	<!-- Bootstrap Select JavaScript -->
	<script src="js/bootstrap-select.min.js"></script>
	
	<script type="text/javascript">
		window.onload = function () {
			$('.selectpicker').selectpicker();
		}
	</script>
	
	<script type="text/javascript">
		$(".form_datetime").datetimepicker({
			format: 'dd M yyyy - hh:ii'
		});
	</script> 
</body>
</html>
