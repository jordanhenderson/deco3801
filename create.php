<?php

require_once 'includes/handlers.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
	exit("Not logged in as admin");
}

$crs = new PCRHandler();

if (isset($_GET['assid'])) {
	$assid = $_GET['assid'];
	$assignment = $crs->getAssignment($assid);
	if ($assignment->isValid()) {
		$asg = &$assignment->getRow();
		if ($asg['CourseID'] != $_SESSION['course_id']) {
			exit("Assignment is for a different course (course_id = $asg[CourseID]). Please log in to that course's page from Moodle to access it.");
		}
	} else {
		exit("Corrupt/Invalid assignment. Please contact site administrator, with code: \"assid=$assid)\"");
	}
	$new = false;
} else {
	$new = true;
	$assignment = new PCRBuilder("Assignments");
	$asg = &$assignment->getRow();
	$assid = '';
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
				<div class="col-md-12">
					<label for="AssignmentName">Assignment Name</label>
					<input class="form-control" id="AssignmentName" <?php echo 'value="'.$asg['AssignmentName'].'"'; ?> name="AssignmentName" type="text"></input>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-12">
					<label for="OpenTime">Open Date</label>
					<input class="form-control form_datetime" id="OpenTime" name="OpenTime" size="24" type="text" <?php echo 'value="'.$asg['OpenTime'].'"'; ?>>
					<p class="help-block">
						Date and time that assignment files are available, and submissions are permitted.
					</p>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-12">
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
				<div class="col-md-12">
					<label for="ReviewsDue">Peer Reviews Due</label>
					<input class="form-control form_datetime" id="ReviewsDue" name="ReviewsDue" size="24" type="text" <?php echo 'value="'.$asg['ReviewsDue'].'"'; ?>>
					<p class="help-block">
						Date and time that students must finish their reviews by.<br>
						Reviews are available from the due date onwards.
					</p>
				</div>
			</div>
			<br>
			<br>
			<div class="row">
				<div class="col-md-6">
					<label for="ReviewsNeeded">Reviews Per Student</label><br>
					<input class="form-control" id="ReviewsNeeded" min="0" max="10" name="ReviewsNeeded" type="number" <?php echo 'value="'.$asg['ReviewsNeeded'].'"'; ?>>
					<p class="help-block">
						The amount of assignments each student will be assigned to review, after the due date.<br>
						Students who have not submitted before the deadline will not be able to create or recieve reviews.
					</p>
				</div>
				<div class="col-md-6">
					<label for="Weight">Weight (%)</label>
					<input class="form-control" id="Weight" min="1" max="100" name="Weight" type="number" <?php echo 'value="'.$asg['Weight'].'"'; ?>>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<label for="ResubmitAllowed">Resubmission Allowed?</label>
					<input  id="ResubmitAllowed" type="checkbox" value="1" <?php if($asg['ResubmitAllowed'] == "1") echo "checked"; ?>>
					<p class="help-block">Toggle the above to prevent or allow students to make multiple assignment submission attempts.</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<label for="NumberTests">Number of Tests</label>
					<input class="form-control" id="NumberTests" type="number" min="0" max="255" value="<?php echo $asg['NumberTests']; ?>">
					<p class="help-block">Enter the number of tests run by the test script provided below.</p>
				</div>
			</div>
		</form>
		<form enctype="multipart/form-data" action="uploadTest.php" method="POST">
		<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label for="file">Unit Testing Files</label><br>
						
							<input type="hidden" name="assignment_id" value="<?php echo $_GET['assid']; ?>">
							<span class="btn btn-default btn-file">
								Browse <input type="file" name="file" id="file">
							</span>
							<input class="btn btn-default btn-primary" type="submit" value="Submit" />
						
						<p class="help-block">Please zip test file(s). The file executed when testing must be named "runtest".</p>
					</div>
				</div>
			</div>
		</form>
		<div align="center">
				<?php
				if ($new) {
					echo '
				<input type="submit" class="btn btn-primary" href="index.php" class="assignmentchange" name="changeAssignment" value="Create">';
				} else {
					echo '
				<input type="submit" class="btn btn-primary assignmentchange" name="changeAssignment" value="Update">
				<input type="submit" class="btn btn-danger assignmentdelete" name="deleteAssignment" value="Delete">';
				}
				echo '
				<input type="submit" class="btn btn-warning" id="reset" value="Reset">';
				if ($new) {
					echo '
				<a href="index.php"><input type="submit" class="btn btn-default" value="Cancel"></a>';
				} else {
					echo '
				<a href="overview.php?assid='.$assid.'"><input type="submit" class="btn btn-default" value="Cancel"></a>';
				}
				?>
				<br><br><br>
			</div>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>

	<!-- Bootstrap datetimepicker JavaScript -->
	<script src="js/bootstrap-datetimepicker.min.js"></script>
	
	<script src="js/json.js"></script>

	<script type="text/javascript">
		$("#reset").click(function() {
			$("#AssignmentName").val(<?php echo "'$asg[AssignmentName]'"; ?>);
			$("#OpenTime").val(<?php echo "'$asg[OpenTime]'"; ?>);
			$("#DueTime").val(<?php echo "'$asg[DueTime]'"; ?>);
			$("#ReviewsDue").val(<?php echo "'$asg[ReviewsDue]'"; ?>);
			$("#Weight").val(<?php echo "'$asg[Weight]'"; ?>);
			$("#ReviewsNeeded").val(<?php echo "'$asg[ReviewsNeeded]'"; ?>);
			$("#ResubmitAllowed").val(<?php echo "'$asg[ResubmitAllowed]'"; ?>);
			$("#NumberTests").val(<?php echo "'$asg[NumberTests]'"; ?>);
		});
		
		$(".form_datetime").datetimepicker({
			format: 'yyyy-mm-dd hh:ii:ss'
		});
		
		$(".assignmentchange").click(function() {
			alert("1");
			var func = $(this).attr("name");
			alert("2");
			switch (func) {
				case "changeAssignment":
					alert("3");
					var funcparams = [
						<?php echo '"'.$assid.'"'; ?>,
						$("#AssignmentName").val(),
						$("#ReviewsNeeded").val(),
						$("#ReviewsDue").val(),
						$("#Weight").val(),
						$("#OpenTime").val(),
						$("#DueTime").val(),
						$("#ResubmitAllowed").is(":checked") ? 1 : 0,
						$("#NumberTests").val()
					];
					break;
				case "deleteAssignment":
					var funcparams = [<?php echo '"'.$assid.'"'; ?>];
					break;
			}
			alert("4");
			var request = {f: func, params: funcparams};
			alert("5");
			$.post("api.php", JSON.stringify(request), function() {
				window.location.replace("index.php");
			});
			alert("6");
		});
	</script> 
</body>
</html>