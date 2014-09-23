<?php

require_once 'includes/handlers.php';
// Pull admin from session var to local var for easier/faster calling
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
	$admin = true;
} else {
	$admin = false;
}

//Enable/Disable Help centre
if (!$admin && (!isset($_SESSION['helpenabled']) || !$_SESSION['helpenabled'])) {
	exit("Help has not enabled by the administrator of this course.");
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
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">
	
	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
	<link href="css/help.css" rel="stylesheet">

</head>

<body>	
<?php include 'header.php'; 

	function seconds2human($s) {
	$m = floor(($s%3600)/60);
	//$m = round(($ss%3600)/60, 0.1);
	$h = floor(($s%86400)/3600);
	$d = floor($s/86400);
	$str = ""; //do i need this? <- yes you do
	if ($d) {
		$str .= "$d days, ";
	}
	if ($h) {
		$str .= "$h hours, ";
	}
	return "$str$m mins";
}
?>
	
	<div class="container">
		<h1>Help Centre</h1>
		<div class="col-lg-12">
			<?php
				if ($admin) { // Help centre options
					echo '
			<h2>Enable/Disable Help Centre for Students</h2>
			<form>
				<select class="selectpicker" data-width="110px">
					<option>Enabled</option>
					<option>Disabled</option>
				</select>
			</form>';
				}
				
				//Get all the questions from the DB to display in the centre
				$questions = $crs->getCourse()->getHelpCentreQuestions();
				
				if (empty($questions)) {
					echo '<p>No questions</p>';
				} else {
			?>
			<h2>Questions</h2>
			<a class="btn btn-xl btn-default" href="addQuestion.php" role="button">Ask a Question</a>
			<!-- This will filter your own questions at some point -->
			<a class="btn btn-xl btn-danger" href="#" role="button">My Questions</a>

			<table class="table">
				<thead >
					<tr class = 'columns'>
						<th>Title</th>
						<th>Last Post</th>
						<th>Original Poster</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php	
					foreach ($questions as $question) {
						if(!$question->isValid()) continue;
						$questionRow = &$question->getRow();
						//Get the last comment to display in the help centre
						$lastpost = $question->getLastComment();
						
						//This sends the question ID in the URL to enable $_GET elsewhere
						echo "
						<tr class='unresolved' data-href='displayQuestion.php?id=$questionRow[QuestionID]'>
						<td ><a href='displayQuestion.php?id=$questionRow[QuestionID]'>$questionRow[Title]</a></td>
						<td>";
						foreach ($lastpost as $last) {
							if(!$last->isValid()) continue;
							//Display last posts individually
							$last = &$last->getRow();
							if (!isset($last['postdate'])) {
								echo 'No postdate specified';
							}
							else {
								$CurrentTime = time();
								$date = date_create_from_format('Y-m-d G:i:s', $last['postdate']);
								$OpenTime = (int) date_format($date, 'U');
								$daysago = $CurrentTime - $OpenTime;
								/*
								Show the last post time + student who posted it
								Subject to change in regards to "hours ago" format
								*/
								echo substr($last['Content'], 0, 28).'<br>'.seconds2human($daysago)." ago by ".$last['StudentName'];
							}
						}
						echo "</td>";
						echo "<td>$questionRow[StudentName]</td>
						<td>";
						//If status == 1, question is resolved
						if ($questionRow['Status'] == 1) {
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
	<script>
$('.unresolved').on("click", function () {
    var href = $(this).data('href');
        document.location = href;
});
</script>
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	
	<!-- Bootstrap Select JavaScript -->
	<script src="js/bootstrap-select.min.js"></script>
	
	<script type="text/javascript">
		window.onload = function () {
			$('.selectpicker').selectpicker();
		}
	</script>
</body>
</html>
