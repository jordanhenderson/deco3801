<?php

require_once('includes/handlers.php');

$crs = new PCRHandler();

$assignid = $_GET['assid'];

$assignment = new Assignment(array("AssignmentID"=>$assignid));
$submission = new Submission(array("AssignmentID"=>$assignid, "StudentID"=>$_SESSION['user_id']), false);

if(!$assignment->isValid() || !$assignment->canResubmit() && $submission->isValid()) {
	header("Location: index.php");
	exit();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Submit - PCR</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
	
	<!-- Breadcrumbs -->
	<!-- jQuery -->
	<link rel="stylesheet" type="text/css" href="css/jquery.rcrumbs.css">
	<script src="js/jquery-1.11.0.js"></script>
	<script src="js/jquery.rcrumbs.js"></script>
	
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<div class="rcrumbs" id="breadcrumbs">
			<ul>
				<li><a href="http://deco3801-14.uqcloud.net">Home</a><span class="divider">></span></li>
				<li><a href="http://deco3801-14.uqcloud.net/overview.php?assid=<?php echo $assignid; ?>">Assignment Overview</a><span class="divider">></span></li>
				<li><a href="#">Assignment Submission</a><span class="divider"></span></li>
			</ul>
		</div>    
		<h1><?php echo $crs->getAssignment($assignid)->getAssignmentName(); ?></h1>
		<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading">
			Assignment Submission
			</div>
			<div class="panel-body">
				<div class="col-sm-12">
		<p class="text-center"><strong>By submitting an assignment using this page you must understand and comply with your institutions misconduct and plagiarism policies.</strong></p>
	      </div>
			<div class="col-sm-6">
			<h2>File Submission</h1>
			<p>Upload your assignment using the file submission method.<br>File types supported: .zip</p>
			<form action="upload.php" method="post" enctype="multipart/form-data">
				<input type="hidden" name="assignment_id" value="<?php echo $_GET['assid']; ?>">
			<span class="btn btn-default btn-file">
			    Browse <input type="file" name="file" id="file">
			</span>
			<button type="submit" value="Submit" class="btn btn-default btn-primary">Submit</button>
			</form>
			
			</div>
			<div class="col-sm-6">
				<h1>Repository Submission</h1>
				<p>Upload your assignment using the repository submission method (GIT).
				</p>
				<form action="submit_repo.php" method="post">
					<input type="hidden" name="assignment_id" value="<?php echo $_GET['assid']; ?>">
				<div class="form-group">
				<label for="repotype">Repository Type</label>
				<select class="form-control" name="repotype" id="repotype">
				    <option value="git">Git</option>
				</select>
				<div>
				
				<div class="form-group">
					<label for="url">URL:</label>
					<input class="form-control" type="text" id="url" name="url">
				</div>
				<div class="form-group">
					<label for="url">Username:</label>
					<input class="form-control" type="text" id="user" name="user">
				</div>
				<div class="form-group">
					<label for="url">Password:</label>
					<input class="form-control" type="text" id="pass" name="pass">
				</div>
				<div class="form-group">
					<input class="btn btn-default btn-primary" type="submit" id="submit">
				</div>
				</form>
			</div>
			</div>
			
		</div>
		
	      </div>
	</div>
	
	</div>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	
	<script>
		$(document).ready(function() {
			$("#breadcrumbs").rcrumbs();
		});
	</script>
	
</body>
</html>
