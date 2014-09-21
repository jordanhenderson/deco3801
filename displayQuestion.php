<?php
require_once 'includes/handlers.php';

$timezone = date_default_timezone_set('Australia/Brisbane');
$date = date('m/d/Y h:i:s a', time());
$id = $_GET['id'];
$crs = new PCRHandler();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Question</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">
	
	<!-- Bootstrap datetimepicker CSS -->
	<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
</head>
<body>
	<?php 
	
	include 'header.php'; 
	//Get contents and data of each question based on the ID
	$question = $crs->getQuestion($id);
	if($question->isValid()) {
		//Get the comments for the question displaying
		$comments = $question->getComments();
		$questionRow = $question->getRow();
		$title = $questionRow["Title"];
		$timeasked = $questionRow["Opendate"];
		$status = $questionRow["Status"];
	}
	?>
	<div class="container">
		<h1><?php 
		echo $title; 
		if ($status == "1") {
			echo '<a class="btn btn-xl btn-success btn-block" role="button" disabled="disabled">Resolved</a></td></tr>';
			}
			else {
				//Place holder not sure what for just yet
			}

		?></h1>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="specfiles"></label>
						<textarea name="content" readonly="readonly" rows="5" cols="110" id="content"><?php echo $questionRow['Content']; ?></textarea>
					</div>
				</div>
				<div class="row">
				<div class="col-md-6">
					<label for="open">Asked On</label>
					<input size="24" type="text" readonly="readonly" value= "<?php echo $timeasked; echo ' by '.$questionRow['StudentName']; ?>" class="form-control form_datetime" id="open" name="open">
				</div>
			</div>

			</div>
			<br>
			<label for="open">Replies</label>
			<br>
			<div class="row">	

				<div class="col-md-6">
					<?php
					foreach ($comments as $comment){
						$commentRow = $comment->getRow();
						//Display each comment as readonly for specific question
						echo "
						<tr class='unresolved'>
						<td></td>
						<td><textarea name='comment' readonly='readonly' rows='5' cols='80' id='comment'>$commentRow[StudentName] Says: $commentRow[Content]</textarea></td>
						<td></td>";
					} ?>
				</div>
			<div align="center">
				<?php
					echo "<a class='btn btn-xl btn-warning' href='addComment.php?Questionid=$questionRow[QuestionID]' role='button'>Reply</a>";
				//If an admin is viewing, gives ability to delete a question
				if ($_SESSION['admin']) {
					echo "
					<input type='submit' class='btn btn-xl btn-danger' id='RemoveQuestion' name='removeQuestion' value='Remove Question'>
					";
				}
				//If status is resolved, then display mark unresolved vice-versa below
				if($status == "1"){
					echo "
					<input type='submit' class='btn btn-xl btn-danger' id='MarkUnresolved' name='markUnresolved' value='Mark Unresolved'>
					";
				}
				else {
					echo "
						<input type='submit' class='btn btn-xl btn-danger' id='MarkResolved' name='markResolved' value='Mark Resolved'>
					";
				}	
				?>
			</div>
		</div>
	</div>

	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
	<!-- Bootstrap datetimepicker JavaScript -->
	<script src="js/bootstrap-datetimepicker.min.js"></script>
	<!-- Bootstrap Select JavaScript -->
	<script src="js/bootstrap-select.min.js"></script>
	<script src="js/json.js"></script>
	<?php
		if($question->isValid()) {
	?>
	<script type="text/javascript">
		$(function() {
			$(":submit").click(function() {
				var func = $(this).attr("name");
				var request = {f: func, params: [<?php echo $questionRow["QuestionID"]; ?>]};
				$.post("api.php", JSON.stringify(request), function() {
					if(func == "markResolved" || func == "markUnresolved") location.reload();
					else window.location.replace("help.php");
				});
			});
		});
	</script>
	<?php
		}
	?>
</body>

</html>
