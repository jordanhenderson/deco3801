<?php

session_start();

require_once 'includes/handlers.php';

if (!isset($_SESSION['helpenabled']) || !$_SESSION['helpenabled']) {
	exit();
}

$crs = new PCRHandler();

?>
<!DOCTYPE html>
<html lang="en">	

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>Help Center</title>
	
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
		<h1>Help Centre</h1>
		<div class="col-lg-12">
			<?php
				$questions = $crs->getCourse()->getHelpCentreQuestions();
				if (is_null($questions)) {
					echo 'no questions';
				} else {
			?>
			<table class="table">
				<thead>
					<td>
						<h2>Questions<h2>
						<a class="btn btn-xl btn-default" href="addQuestion.php" role="button">Ask a Question</a>
						<a class="btn btn-xl btn-danger" href="#" role="button">My Questions</a>
					</td>
				</thead>
				<tbody>
					<tr>
						<th>Title</th>
						<th>Assessment</th>
						<th>Last Post</th>
						<th>Student</th>
					</tr>
				<?php
					foreach ($questions as $question){
						$question = $question->jsonSerialize();
						echo "
						<tr class='unresolved'>
							<td><a href='placeholderlink'>$question[Title]</a></td>
							<td>$question[Title]</td>
							<td>$question[Title]</td>
							<td>$question[StudentID]</td>
						</tr>";
					}
					echo '
				</tbody>
			</table>';
				}
			?>
		</div>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>
	
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>