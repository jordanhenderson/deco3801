<?php

session_start();

require_once 'includes/handlers.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
	exit("Not logged in as admin");
}

$crs = new PCRHandler();

if (isset($_REQUEST['assid'])) { // TODO - restrict access to other courses assignments
	$asg = $crs->getAssignment($_REQUEST['assid']);
	if($asg->isValid()) {
		$asg = $asg->getRow();
	} else {
		die();
	}
	$new = false;
} else {
	$new = true;
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
				<div class="col-md-6">
					<label for="open">Open Date</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['OpenTime'].'"'; ?> class="form-control form_datetime" id="open">
					<p class="help-block">
						Date and time that assignment files are available, and submissions are permitted.
					</p>
				</div>
				<div class="col-md-6">
					<label for="due">Due Date</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['DueTime'].'"'; ?> class="form-control form_datetime" id="due">
					<p class="help-block">
						Date and time that the assignment must be submitted before.<br>
						A late submission will be declared to both the student and teacher.
					</p>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<label for="review-open">Reviewing Open</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['ReviewOpenTime'].'"'; ?> class="form-control form_datetime" id="review-open">
					<p class="help-block">
						Date and time that may begin reviewing other students code.<br>
						This may be set to before the due date, though it is not reccomended.
					</p>
				</div>
				<div class="col-md-6">
					<label for="review-due">Reviewing Closed</label>
					<input size="24" type="text" <?php echo 'value="'.$asg['ReviewsVisibleTime'].'"'; ?> class="form-control form_datetime" id="review-due">
					<p class="help-block">Date and time that students must finish their reviews by.</p>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="specfiles">Assignment Files</label>
						<input type="file" id="specfiles">
						<p class="help-block">PDF or zip containing the assignment specifications.</p>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="testfiles">Unit Testing Files</label>
						<input type="file" id="testfiles">
						<p class="help-block">Please zip test file(s). The file executed when testing must be named "runtest"</p>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
					<label for="weight">Weight (%)</label>
					<input <?php echo 'value="'.$asg['Weight'].'"'; ?> class="form-control" type="number" id="weight" min="1" max="100">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
					<label for="reviewnum">Reviews Per Student</label>
					<select class="selectpicker" data-width="120px" id="reviewnum">
						<option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10</option>
					</select>
				</div>
			</div>
		</form>
		<div align="center">
				<a class="btn btn-primary" href="#" role="button">Submit</a>&nbsp;
				<a class="btn btn-info" href="#" role="button">Save</a>&nbsp;
				<a class="btn btn-warning" href="#" role="button">Reset</a>
		</div>
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
