<?php

session_start();

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
		<h1>Setup New Assignment</h1>
		<form role="form" >
			<div class="row">
				<div class="col-md-6">
					<label for="name">Assignment Name</label>
					<input class="form-control" type="text" id="name">
				</div>
				<div class="col-md-6">
					<label for="course">Course</label>
					<br>
					<select class="selectpicker" data-width="120px" id="course">
						<option>CSSE1001</option>
						<option>CSSE2002</option>
						<option>CSSE2310</option>
					</select>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<label for="open">Open Date</label>
					<input size="24" type="text" value="17 Sep 2014 - 14:30" class="form-control form_datetime" id="open">
				</div>
				<div class="col-md-6">
					<label for="due">Due Date</label>
					<input size="24" type="text" value="18 Sep 2014 - 14:30" class="form-control form_datetime" id="due">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="specfiles">Assignment Files</label>
						<input type="file" id="specfiles">
						<p class="help-block">Please zip files.</p>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="testfiles">Unit Testing Files</label>
						<input type="file" id="testfiles">
						<p class="help-block">Please zip files.</p>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
					<label for="weight">Weight</label>
					<input value="10%" class="form-control" type="text" id="weight">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
					<label for="peer">Choose when students can peer review</label>
					<br>
					<select class="selectpicker" data-width="120px" id="peer">
						<option>Any time</option>
						<option>After at least 1 submission</option>
						<option>After due date</option>
					</select>
				</div>
				<div class="col-md-3">
					<label for="feedback">Choose when students can view feedback</label>
					<br>
					<select class="selectpicker" data-width="120px" id="feedback">
						<option>Any time</option>
						<option>After at least 1 submission</option>
						<option>After due date</option>
					</select>
				</div>
				<div class="col-md-3">
					<label for="multiple">Allow Multiple Submissions</label>
					<select class="selectpicker" data-width="120px" id="multiple">
						<option>Yes</option>
						<option>No</option>
					</select>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
					<label for="reviewnum">Reviews Per Student</label>
					<select class="selectpicker" data-width="120px" id="reviewnum">
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
						<option>6</option>
						<option>7</option>
						<option>8</option>
						<option>9</option>
						<option>10</option>
					</select>
				</div>
			</div>
		</form>
		<div align="center">
				<a class="btn btn-primary" href="#" role="button">Submit</a>
				<a class="btn btn-info" href="#" role="button">Save</a>
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