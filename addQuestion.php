<?php

session_start();

$timezone = date_default_timezone_set('Australia/Brisbane');
$date = date('m/d/Y h:i:s a', time());

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<script>
	//Basic validation, will make this better later
	function checkForm(){

		if (document.qF.title.value == null || document.qF.title.value == "") {
			window.alert("Please add a title proceed");
			return false;
		} else if (document.qF.content.value == null || document.qF.content.value == "") {
			window.alert("Please add some content to your question to proceed");
			return false;
		} else {
			return true;
		}	
	}
	</script>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>New Question</title>

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
		<form onsubmit="return checkForm()" name="qF" action="storeQuestion.php" method="post">
			<div class="row">
				<div class="col-md-6">
					<label for="title">Question Title</label>
					<input class="form-control" name="title" type="text" id="title">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<label for="content">Question Content</label>
					<textarea class="form-control" name="content" rows="15" id="content"></textarea>
				</div>
			</div>
			<br>
			<div align="center">
				<input class="btn btn-primary" type="submit" value="Submit">
				<a class="btn btn-warning" onclick="#" role="button">Reset</a>
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
	
	<script type="text/javascript">
		function reset() {
			$("#title").html("");
			$("#content").html("");
		}
	</script>
</body>
</html>