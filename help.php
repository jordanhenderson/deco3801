<?php 
		
		require_once 'blti/blti.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>Help Center</title>
	
	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">
	
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
		function helpEnabled($courseID){
		$con=mysqli_connect("localhost","root",null,"deco3801");
		$sql = "SELECT HelpEnabled FROM `course` WHERE CourseID=$courseID";
		$query = mysqli_query($con, $sql);
		while($row = mysqli_fetch_array($query)){	
			$help = $row['HelpEnabled'];
		}
	}
		helpEnabled(00001);
	?>
	
	<div class="container">
		<h1>Help Centre</h1>
		<div class="col-lg-12">
			<table class="table">
				<thead>
					<td>
					<h2>Questions<h2>
						<a class="btn btn-xl btn-default" href="addQuestion.php" role="button">Ask a Question</a>
						<a class="btn btn-xl btn-danger" href="#" role="button">My Questions</a>
					<td>
					<tr>
						<th>Title</th>
						<th>Assessment</th>
						<th>Requested on</th>
					</tr>
				</thead>
				<tbody>
					<tr class="submitted">
					</tr>
					<tr class="unsubmitted">
					</tr>
					<tr class="unsubmitted">
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>
	
	<script type="text/javascript">
		window.onload = function () {
			$('.selectpicker').selectpicker();
		}
	</script>
	
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	
	<!-- Bootstrap Select JavaScript -->
	<script src="js/bootstrap-select.min.js"></script>
</body>
</html>