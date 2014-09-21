<?php

require_once 'includes/handlers.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
	exit("Not logged in as admin");
}

$crs = new PCRHandler();

if (isset($_REQUEST['assid'])) {
	$assignment = $crs->getAssignment($_REQUEST['assid']);
	if ($assignment->isValid()) {
		$asg = &$assignment->getRow();
		if ($asg['CourseID'] != $_SESSION['course_id']) {
			exit("Assignment is for a different course (course_id = $asg[CourseID]). Please log in to that course's page from Moodle to access it.");
		}
	} else {
		exit("Corrupt/Invalid assignment. Please contact site administrator, with code: \"assid=$_REQUEST[assid])\"");
	}
	$new = false;
} else {
	$new = true;
	$assignment = new PCRBuilder("Assignments");
	$asg = &$assignment->getRow();
}

if (isset($_POST['create']) || isset($_POST['update'])) {
	$asg['CourseID'] = $_SESSION['course_id'];
	$asg['AssignmentName'] = $_POST['AssignmentName'];
	$asg['DueTime'] = $_POST['DueTime'];
	$asg['OpenTime'] = $_POST['OpenTime'];
	$asg['ReviewsDue'] = $_POST['ReviewsDue'];
	$asg['Weight'] = $_POST['Weight'];
	$asg['ReviewsNeeded'] = $_POST['ReviewsNeeded'];
	//$asg['TestFiles'] = $_POST['TestFiles'];
	//$asg['AssignmentFiles'] = $_POST['AssignmentFiles'];
	$assignment->commit();
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
	<?php include 'header.php';
	
	foreach ($_POST as $key => $value) {
		echo "$key = $value\n";
	}// debug^
	
	?>
	
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
				<div class="col-md-8">
					<label for="AssignmentName">Assignment Name</label>
					<input class="form-control" id="AssignmentName" <?php echo 'value="'.$asg['AssignmentName'].'"'; ?> name="AssignmentName" type="text"></input>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-8">
					<label for="OpenTime">Open Date</label>
					<input class="form-control form_datetime" id="OpenTime" name="OpenTime" size="24" type="text" <?php echo 'value="'.$asg['OpenTime'].'"'; ?>>
					<p class="help-block">
						Date and time that assignment files are available, and submissions are permitted.
					</p>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-8">
					<label for="DueTime">Due Date</label>
					<input class="form-control form_datetime" id="DueTime" name="DueTime" size="24" type="text" <?php echo 'value="'.$asg['DueTime'].'"'; ?>>
					<p class="help-block">
						Date and time that the assignment must be submitted before, without being declared to both the student and teacher.<br>
						The students may begin peer reviewing <b>1 day</b> after this time.<br>
						A student who has not submitted before peer review begins can not participate in the peer review.<br>
						Students cannot submit after peer review begins.
					</p>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-8">
					<label for="ReviewsDue">Peer Reviews Due</label>
					<input class="form-control form_datetime" id="ReviewsDue" name="ReviewsDue" size="24" type="text" <?php echo 'value="'.$asg['ReviewsDue'].'"'; ?>>
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
						<label for="AssignmentFiles">Assignment Files TODO TODO</label>
						<input id="AssignmentFiles" name="AssignmentFiles" type="file">
						<p class="help-block">PDF or zip containing the assignment specifications, for students to download.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="TestFiles">Unit Testing Files TODO TODO</label>
						<input id="TestFiles" name="TestFiles" type="file">
						<p class="help-block">Please zip test file(s). The file executed when testing must be named "runtest".</p>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-4">
					<label for="ReviewsNeeded">Reviews Per Student</label><br>
					<input class="form-control" id="ReviewsNeeded" min="0" max="10" name="ReviewsNeeded" type="number" <?php echo 'value="'.$asg['ReviewsNeeded'].'"'; ?>>
					<p class="help-block">
						The amount of assignments each student will be assigned to review, after the due date.<br>
						Students who have not submitted before the deadline will not be able to create or recieve reviews.
					</p>
				</div>
				<div class="col-md-4">
					<label for="Weight">Weight (%)</label>
					<input class="form-control" id="Weight" min="1" max="100" name="Weight" type="number" <?php echo 'value="'.$asg['Weight'].'"'; ?>>
				</div>
			</div>
			<div align="center">
				<?php
				if ($new) {
					echo '
				<button class="btn btn-primary" type="submit" value="create" name="create">Create</button>';
				} else {
					echo '
				<button class="btn btn-primary" type="submit" value="update" name="update">Update</button>';
				}
				?>
				<div class="btn btn-warning" id="Reset">Reset</div>
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
		$("#Reset").click(function() {
			$("#AssignmentName").val(<?php echo "'$asg[AssignmentName]'"; ?>);
			$("#OpenTime").val(<?php echo "'$asg[OpenTime]'"; ?>);
			$("#DueTime").val(<?php echo "'$asg[DueTime]'"; ?>);
			$("#ReviewsDue").val(<?php echo "'$asg[ReviewsDue]'"; ?>);
			$("#Weight").val(<?php echo "'$asg[Weight]'"; ?>);
			$("#ReviewsNeeded").val(<?php echo "'$asg[ReviewsNeeded]'"; ?>);
			
			$("#TestFiles").replaceWith($("#TestFiles").clone());
			$("#AssignmentFiles").replaceWith($("#AssignmentFiles").clone());
		});
		
		$(".form_datetime").datetimepicker({
			format: 'yyyy-mm-dd hh:ii:ss'
		});
	</script> 
</body>
</html>
