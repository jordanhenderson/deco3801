<?php
session_start();
$timezone = date_default_timezone_set('Australia/Brisbane');
$date = date('m/d/Y h:i:s a', time());
$id = $_GET["QuestionID"];
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Add New Comment</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<h1>Ask a New Question</h1>
		<form name="cF" id="cF" action="api.php" method="post" data-function="addComment">
			<div class="row">
			</div>
			<br>
			<div class="row">
				<div class="col-md-6">
					<label for="specfiles">Question Content</label>
						<textarea class="form-control" name="comment" rows="15" cols="89" id="content"></textarea>
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
	
	<!-- Bootstrap Select JavaScript -->
	<script src="js/bootstrap-select.min.js"></script>
	
	<script type="text/javascript">
		$(function() {
			$('#cF').submit(function() {
				var form = $(this);
				//Use the action= property for ajax submission
				var fullname = '<?php echo $_SESSION['userfullname'];?>'
				var stnid = '<?php echo $_SESSION['user_id'];?>'
				var url = form.attr('action');
				var func = form.data('function');
				alert(func);
				var request = {f: func, params: [<?php echo $id; ?>, stnid, fullname, $("#content").val()]};
				//Post the serialized form.
				$.post(url, JSON.stringify(request), function(data) {
					alert(JSON.stringify(data));
					//Handle submission.
					document.location.href = "displayQuestion.php?id=<?php echo $id; ?>";
				});
				
				
				return false;
			});
		});
	</script>
	<script type="text/javascript">
		window.onload = function () {
			$('.selectpicker').selectpicker();
		}
	</script>
</body>
</html>
