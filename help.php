<?php
session_start();

require_once 'includes/db.php';
require_once 'includes/handlers.php';
//Enable/Disable Help centre
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
</head>

<body>	
	<?php include 'header.php'; ?>
	
	<div class="container">
		<h1>Help Centre</h1>
		<div class="col-lg-12">
			<?php
			//Get all the questions from teh DB to display in the centre
				$questions = $crs->getCourse()->getHelpCentreQuestions();
				
				if (is_null($questions)) {
					echo 'no questions';
				} else {
			?>
			<h2>Questions</h2>
			<a class="btn btn-xl btn-default" href="addQuestion.php" role="button">Ask a Question</a>
			<!-- This will filter your own questions at some point -->
			<a class="btn btn-xl btn-danger" href="#" role="button">My Questions</a>

			<table class="table">
				<thead>
					<tr>
						<th>Title</th>
						<th>Last Post</th>
						<th>Student</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php	
					foreach ($questions as $question){
						//Get each question from array of questions to echo in the centre
						$question = $question->jsonSerialize();
						//Get the last comment to display in the help centre
						$lastpost = $crs->getQuestion($question['QuestionID'])->getLastComment($question['QuestionID']);
						
						//This sends the question ID in the URL to enable $_GET elsewhere
						echo "
						<tr class='unresolved'>
						<td><a href='displayQuestion.php?id=$question[QuestionID]'>$question[Title]</a></td>
						<td>";
						foreach($lastpost as $last) {
							//Display last posts individually
							$last = $last->jsonSerialize();	
							if(!isset($last['postdate'])){
								echo "No postdate specified";
							}
							else {
								/*Show the last poste time + student who posted it
								/Subject to change in regards to "hours ago" format
								*/
								echo $last['postdate']." by ".$last['StudentName'];
							}
						}
						echo "</td>";
						echo "<td>$question[StudentName]</td>
						<td>";
						//If status is one -> question is resolved
						if($question['Status'] == 1){
							echo '<a class="btn btn-xl btn-success btn-block" role="button" disabled="disabled">Resolved</a></td></tr>';
						}
						else {
							echo '<a class="btn btn-xl btn-danger btn-block" role="button" disabled="disabled" >Unresolved</a></td></tr>';
						}	
					} ?>
				</tbody>	
			</table>
				<?php } ?>
		</div>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>
	
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>