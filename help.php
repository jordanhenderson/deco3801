<?php
$timezone = date_default_timezone_set('Australia/Brisbane');
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
	
	<title>Help Centre</title>
	
	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">
	
	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
	<link href="css/help.css" rel="stylesheet">
	
	<!-- Breadcrumbs -->
	<!-- jQuery -->
	<link rel="stylesheet" type="text/css" href="css/jquery.rcrumbs.css">
	<script src="js/jquery-1.11.0.js"></script>
	<script src="js/jquery.rcrumbs.js"></script>
</head>

<body>	
<?php include 'header.php'; 

	function seconds2human($s) {
		$str = " ";
	$m = floor(($s%3600)/60);
	$h = floor(($s%86400)/3600);
	$d = floor($s/86400);
	if ($d) {
		$str .= "$d days, ";
	}
	if ($h) {
		$str .= "$h hours, ";
	}
	return "$str$m mins";
}
$helpstatus = $_SESSION['helpenabled'];
?>
	
	<div class="container">
		<div class="rcrumbs" id="breadcrumbs">
			<ul>
				<li><a href="http://deco3801-14.uqcloud.net">Home</a><span class="divider">></span></li>
				<li><a href="#">Help Centre</a><span class="divider"></span></li>
			</ul>
		</div>
		
		<h1>Help Centre</h1>
		<div class="col-lg-12">
			<?php
				//if the help centre isn't on give the admin the option to turn it on
				if ($admin && (isset($_SESSION['helpenabled']) && $_SESSION['helpenabled'] == 0)) {

					echo "
					<input type='submit' class='btn btn-xl btn-success' id='TurnHelpCentreOn' name='toggleHelp' value='Turn Help Centre On'>
					";
					echo "<br></br>";
					echo "The help centre is currently disabled for students";
					
				}
				//If the help centre is on give admin the option to turn it off
				if ($admin && (isset($_SESSION['helpenabled']) && $_SESSION['helpenabled'] == 1)){
					echo "
					<input type='submit' class='btn btn-xl btn-success' id='TurnHelpCentreOff' name='toggleHelp' value='Turn Help Centre Off'>
					";
				}
				//Get all the questions from the DB to display in the centre
				$questions = $crs->getCourse()->getHelpCentreQuestions();
				
				
			?>
			<h2>Questions</h2>
			<a class="btn btn-xl btn-default" href="addQuestion.php" role="button">Ask a Question</a>
			<br><br>
			<?php 
			if (empty($questions)) {
					echo '<p>Currently there are no questions here, why not try asking one!</p>';
				} else {
					?>
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
						if(empty($lastpost)){
							echo 'No answers yet';
						}
						foreach ($lastpost as $last) {
							if(!$last->isValid()) continue;
							//Display last posts individually
							$last = &$last->getRow();
								$CurrentTime = time();
								$date = date_create_from_format('Y-m-d G:i:s', $last['postdate']);
								$OpenTime = (int) date_format($date, 'U');
								$daysago = seconds2human($CurrentTime - $OpenTime);

								echo  $daysago." ago by <strong><br>".$last['StudentName']."</strong>";
						}
						echo "</td>";
						echo "<td>$questionRow[StudentName]</td>
						<td class='status'>";
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
	
	<script>
$('.unresolved').on("click", function () {
    var href = $(this).data('href');
        document.location = href;
});

</script>
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	<script type="text/javascript">
		$(function() {
			$(":submit").click(function() {
				var func = $(this).attr("name");
				var status = "<?php echo $helpstatus; ?>";
				var request = {f: func, params: [status]};
				$.post("api.php", JSON.stringify(request), function() {
					window.location.replace("index.php");
				});
			});
		});
	</script>
	<!-- Bootstrap Select JavaScript -->
	<script src="js/bootstrap-select.min.js"></script>
	
	<script type="text/javascript">
		window.onload = function () {
			$('.selectpicker').selectpicker();
		}
	</script>
	
	<script>
		$(document).ready(function() {
			$("#breadcrumbs").rcrumbs();
		});
	</script>
</body>
</html>
