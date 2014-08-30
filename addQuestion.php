<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
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
	<?php 
		require 'header.php'; 
		$timezone = date_default_timezone_set('Australia/Melbourne');
		$date = date('m/d/Y h:i:s a', time());
	?>
	
	
	<div class="container">
		<h1>Ask a New Question</h1>
		<form role="form" >
			<div class="row">
				<div class="col-md-6">
					<label for="name">Question Title</label>
					<input class="form-control" type="text" id="name">
				</div>
				<div class="col-md-6">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<label for="open">Open Date</label>
					<input size="24" type="text" value= "<?php echo $date ?>" class="form-control form_datetime" id="open">
				</div>
				<div class="col-md-6">
					<label for="assess">Assessment Piece</label><br>
					<select class="selectpicker" data-width="130px" id="course">
						<option>Assignment 1</option>
						<option>Assignment 2</option>
						<option>Assignment 3</option>
						<option>Assignment 4</option>
					</select>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="specfiles">Question Content</label>
						<textarea name="content" rows="5" cols="80" id="content"></textarea>

					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
				</div>
				<div class="col-md-3">
				</div>
				<div class="col-md-3">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
					
				</div>
			</div>
		</form>
		<div align="center">
				<a class="btn btn-primary" href="#" role="button">Submit</a>
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