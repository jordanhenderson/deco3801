<?php

session_start();

if (!isset($_SESSION['helpenabled']) || !$_SESSION['helpenabled']) {
	header('Location: invalid.php'); //
	exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>Review Hub - PCR</title>
	
	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
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
		<h1>Review Hub</h1>
		<div class="col-lg-12">
			<h2>Assignments to Review</h2>
			<table class="table">
				<thead>
					<tr>
						<th>Assignment Name</th>
						<th>Course</th>
						<th>Open Date</th>
						<th>Due Date</th>
						<th>Weight</th>
					</tr>
				</thead>
				<tbody>
					<tr class="submitted">
						<td>Assignment 1</td>
						<td>CSSE1001</td>
						<td>29/8/14</td>
						<td>5/9/14</td>
						<td>10%</td>
						<td>
							<a class="btn btn-xs btn-default" href="#" role="button">Mark</a>
							<a class="btn btn-xs btn-danger" href="#" role="button">View</a>
						</td>
					</tr>
					<tr class="unsubmitted">
						<td>Assignment 2</td>
						<td>CSSE1001</td>
						<td>5/9/14</td>
						<td>12/9/14</td>
						<td>15%</td>
						<td>
							<a class="btn btn-xs btn-default" href="#" role="button">Mark</a>
							<a class="btn btn-xs btn-danger" href="#" role="button">View</a>
						</td>
					</tr>
					<tr class="unsubmitted">
						<td>Assignment 3</td>
						<td>CSSE1001</td>
						<td>12/9/14</td>
						<td>19/9/14</td>
						<td>15%</td>
						<td>
							<a class="btn btn-xs btn-default" href="#" role="button">Mark</a>
							<a class="btn btn-xs btn-danger" href="#" role="button">View</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>
	
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>