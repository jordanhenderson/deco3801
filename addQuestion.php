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
		$(document).ready(function() {
			$('#qF').submit(function(msg)){
			}
		 var data = $('#qF').serializeArray();
		 $.post("api.php", $(this).serialize(),function(data){
		 	  alert(data); //post check to show that the mysql string is the same as submit           
		}	
        return false; // return false to stop the page submitting. You could have the form action set to the same PHP page so if people dont have JS on they can still use the form
    });
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
	<?php include 'header.php';?>
	
	<div class="container">
		<h1>Ask a New Question</h1>
		<form  name="qF" id="qF" method="post" action="api.php">
			<div class="row">
				<div class="col-md-6">
					<label for="title">Question Title</label>
					<input class="form-control" name="question" type="text" id="QTitle">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<label for="content">Question Content</label>
					<textarea class="form-control" style="resize: vertical;" name="QContent" rows="15" id="QContent"></textarea>
				</div>
			</div>
			<br>
			<div align="center">
				<input class="btn btn-primary" type="submit" value="Submit" name="submit">
				<input class="btn btn-warning" onclick="reset();" value="Reset">
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