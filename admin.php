<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>Admin - PCR</title>
	
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
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Peer Code Review</a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Dashboard</a></li>
					<li><a href="#">Help Centre</a></li>
					<li><a href="#">Admin</a></li>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="container">
		<h1>Admin Panel</h1>
		<div class="col-lg-12">
			<h2>Assignments</h2>
			<table class="table">
				<thead>
					<tr>
						<th>Title</th>
						<th>Course</th>
						<th>Open Date</th>
						<th>Due Date</th>
						<th>Weight</th>
						<th><a class="btn btn-xs btn-primary" href="create.php" role="button">Create New</a></th>
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
							<a class="btn btn-xs btn-default" href="create.php" role="button">Edit</a>
							<a class="btn btn-xs btn-danger" href="#" role="button">Delete</a>
						</td>
					</tr>
					<tr class="unsubmitted">
						<td>Assignment 2</td>
						<td>CSSE1001</td>
						<td>5/9/14</td>
						<td>12/9/14</td>
						<td>15%</td>
						<td>
							<a class="btn btn-xs btn-default" href="create.php" role="button">Edit</a>
							<a class="btn btn-xs btn-danger" href="#" role="button">Delete</a>
						</td>
					</tr>
					<tr class="unsubmitted">
						<td>Assignment 3</td>
						<td>CSSE1001</td>
						<td>12/9/14</td>
						<td>19/9/14</td>
						<td>15%</td>
						<td>
							<a class="btn btn-xs btn-default" href="create.php" role="button">Edit</a>
							<a class="btn btn-xs btn-danger" href="#" role="button">Delete</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-lg-12">
			<h2>Course Settings</h2>
			<table class="table">
				<thead>
					<tr>
						<th>Course</th>
						<th>Enable Help Centre</th>
					</tr>
				</thead>
				<tbody>
					<tr class="submitted">
						<td>CSSE1001</td>
						<td>
							<select class="selectpicker" data-width="110px">
								<option>Enabled</option>
								<option>Disabled</option>
							</select>
						</td>
					</tr>
					<tr class="unsubmitted">
						<td>CSSE2002</td>
						<td>
							<select class="selectpicker" data-width="110px">
								<option>Enabled</option>
								<option>Disabled</option>
							</select>
						</td>
					</tr>
					<tr class="unsubmitted">
						<td>CSSE2310</td>
						<td>
							<select class="selectpicker" data-width="110px">
								<option>Enabled</option>
								<option>Disabled</option>
							</select>
						</td>
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