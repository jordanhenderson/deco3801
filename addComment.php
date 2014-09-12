<?php
session_start();
$timezone = date_default_timezone_set('Australia/Brisbane');
$date = date('m/d/Y h:i:s a', time());

// I don't know what this is, but it seems like a bad idea storing
// something as vague as this in the session. Consider using only GET. -Ad
$id = $_GET['id'];
$_SESSION['id'] = $id;
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<script>
	//Basic validation, with popup, will change to update error msg
	function checkForm(){
		if (document.qF.content.value == null || document.qF.content.value == ""){
			window.alert("Please add some content to your question to proceed");
			return false;
		}
		else{
			return true;
		}	
	}
	</script>
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
		<h1>Ask a New Question</h1>
		<form onsubmit="return checkForm()" name="qF"  action="storeComment.php" method="post">
			<div class="row">
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<label for="specfiles">Question Content</label>
						<textarea class="form-control" name="content" rows="15" cols="89" id="content"></textarea>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
					<!--placeholder for future content perhaps-->
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
					<!--placeholder for future content perhaps-->
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
				<!--placeholder for future content perhaps-->
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
				<!--placeholder for future content perhaps-->
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-3">
					<!--placeholder for future content perhaps-->
				</div>
			</div>
			<div align="center">
				<input class="btn btn-primary" type="submit" value="Submit"></a>
				<a class="btn btn-warning" href="#" role="button">Reset</a>
		</form>
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