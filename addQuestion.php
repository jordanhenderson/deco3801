<?php

session_start();

$timezone = date_default_timezone_set('Australia/Brisbane');
$date = date('m/d/Y h:i:s a', time());

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<script>
		$(function() {
			$('form').submit(function() {
				var form = $(this);
				//Use the action= property for ajax submission
				var url = form.attr('action');
				var params = form.serializeArray();
				var func = form.data('function');
				var request = {f: func, params: params};
				//Post the serialized form.
				$.post(url, request, function(data) {
					//Handle submission.
					
				});
				
				return false;
			});
		});
	</script>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>New Question</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
</head>

<body>
	<?php include 'header.php';?>
	
	<div class="container">
		<h1>Ask a New Question</h1>
		<form name="qF" id="qF" method="post" action="api.php" data-function="storeNewQuestion">
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
	
	<!-- Bootstrap Select JavaScript -->
	<script src="js/bootstrap-select.min.js"></script>
	
	<script type="text/javascript">
		window.onload = function () {
			$('.selectpicker').selectpicker();
		}
	</script>
	
	<script type="text/javascript">
		function reset() {
			$("#title").html("");
			$("#content").html("");
		}
	</script>
</body>
</html>
