<?php

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

if (isset($_POST['C'])) {
	header('location: search.php');
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
		<form method="post">
			<div class="row">
				<div class="col-md-6">
					<label for="AssignmentName">Assignment Name</label>
					<input class="form-control" type="text" id="AssignmentName" <?php echo 'value="'.$asg['AssignmentName'].'"'; ?>></input>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-4">
					<label for="OpenTime">Open Date</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['OpenTime'].'"'; ?> class="form-control form_datetime" id="OpenTime">
					<p class="help-block">
						Date and time that assignment files are available, and submissions are permitted.
					</p>
				</div>
				<div class="col-md-4">
					<label for="DueTime">Due Date</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['DueTime'].'"'; ?> class="form-control form_datetime" id="DueTime">
					<p class="help-block">
						Date and time that the assignment must be submitted before, without being declared to both the student and teacher.<br>
						The students may begin peer reviewing <b>1 day</b> after this time.<br>
						A student who has not submitted before peer review begins can not participate in the peer review.<br>
						Students cannot submit after peer review begins.
					</p>
				</div>
				<div class="col-md-4">
					<label for="ReviewsDue">Peer Reviews Due</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['ReviewsDue'].'"'; ?> class="form-control form_datetime" id="ReviewsDue">
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
					<label for="Weight">Weight (%)</label>
					<input <?php echo 'value="'.$asg['Weight'].'"'; ?> class="form-control" type="number" id="Weight" min="1" max="100">
				</div>
				<div class="col-md-4">
					<label for="ReviewsNeeded">Reviews Per Student</label><br>
					<input <?php echo 'value="'.$asg['ReviewsNeeded'].'"'; ?> class="form-control" type="number" id="ReviewsNeeded" min="0" max="10">
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
				<button class="btn btn-primary" type="submit" value="submit">Create</button>';
				} else {
					echo '
				<button class="btn btn-primary" type="submit" value="submit">Update</button>';
				}
				?>
				<button class="btn btn-warning Reset">Reset</button>
				<input class="btn btn-warning Reset">Reset</input>
				<div class="btn btn-warning Reset">Reset</div>
				<a class="btn btn-default" href="index.php">Cancel</a>
				<br><br>
			</div>
		</form>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>

	<!-- Bootstrap datetimepicker JavaScript -->
	<script src="js/bootstrap-datetimepicker.min.js"></script>
	
	<script type="text/javascript">
		//Reset form.
		$(".Reset").click(function() {
			alert("clicked reset");
			$("#AssignmentName").val(<?php echo "'$asg[AssignmentName]'"; ?>)
			$("#OpenTime").val(<?php echo "'$asg[OpenTime]'"; ?>)
			$("#DueTime").val(<?php echo "'$asg[DueTime]'"; ?>)
			$("#ReviewsDue").val(<?php echo "'$asg[ReviewsDue]'"; ?>)
			$("#specfiles").val(<?php echo "'???'"; ?>)
			$("#testfiles").val(<?php echo "'???'"; ?>)
			$("#Weight").val(<?php echo "'$asg[Weight]'"; ?>)
			$("#ReviewsNeeded").val(<?php echo "'$asg[ReviewsNeeded]'"; ?>)
		});
		
		$(".form_datetime").datetimepicker({
			format: 'yyyy-mm-dd hh:ii:ss'
		});
	</script> 
</body>
</html>
