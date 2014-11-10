<?php
require_once 'includes/handlers.php';

$timezone = date_default_timezone_set('Australia/Brisbane');
$date = date("Y-m-d  H:i:s", time());
$id = $_GET['id'];
$crs = new PCRHandler();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Question</title>
	<script src="ckeditor/ckeditor.js"></script>
	<script>CKEDITOR.timestamp='ABCD';</script>
	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	
	<!-- Bootstrap Select CSS -->
	<link href="css/bootstrap-select.min.css" rel="stylesheet">
	<!-- Bootstrap datetimepicker CSS -->
	<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
	<link href="css/displayQ.css" rel="stylesheet">
	<link href="css/error.css" rel="stylesheet">
</head>
<body>
	<?php 
	$count = 0;

	function seconds2human($s) {
		$str = " ";
	$m = floor(($s%3600)/60);
	//$m = round(($ss%3600)/60, 0.1);
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

	include 'header.php'; 
	//Get contents and data of each question based on the ID
	$question = $crs->getQuestion($id);
	if($question->isValid()) {
		//Get the comments for the question displaying
		$comments = $question->getComments();
		$questionRow = &$question->getRow();
		$title = $questionRow["Title"];
		$timeasked = $questionRow["Opendate"];
		$status = $questionRow["Status"];
	}
	$CurrentTime = time();
	$date = date_create_from_format('Y-m-d G:i:s', $timeasked);
	$OpenTime = (int) date_format($date, 'U');
	$daysago = seconds2human($CurrentTime - $OpenTime);
	?>
	<div class="container">
		<div class="content">
		<h1><?php 
		if ($status == "1") {
			echo '<a class="btn btn-xl btn-success btn-block" role="button" disabled="disabled">Resolved</a></td></tr>';
			}
			else {
				//Place holder not sure what for just yet
			}
				echo "<div id = buttons>";
				//If an admin is viewing, gives ability to delete a question
				if (isset($_SESSION['admin'])) {
					echo "
					<input type='submit' class='btn btn-xl btn-danger' id='RemoveQuestion' name='removeQuestion' value='Remove Question'>
					";
				}
				//If status is resolved, then display mark unresolved vice-versa below
				if($status == "1"){
					echo "
					<input type='submit'  class='btn btn-xl btn-danger' id='MarkUnresolved' name='markUnresolved' value='Mark Unresolved'>
					";
				}
				else {
					echo "
						<input type='submit' class='btn btn-xl btn-danger' id='MarkResolved' name='markResolved' value='Mark Resolved'>
					";
				}	
				echo "</div>";
				echo "<div id = title>".$title."</div>";

		?></h1>
			<div class="row">
					<div class="form-group">
						<label for="specfiles"></label>
						<?php echo "	
						<div class='name'>$questionRow[StudentName]<div class='date'>".$daysago." ago</div></div>
						<div class='comment'><td>$questionRow[Content]</div>";
						 ?>
				</div>
				

			</div>
			<br>
			<label for="open">Answers</label>
			<br>
			<div class="row">	

					<?php
					foreach ($comments as $comment){
						$commentRow = &$comment->getRow();
						//Set time ago for each individual comment post
						$timeasked = $commentRow['postdate'];
						$CurrentTime = time();
						$date = date_create_from_format('Y-m-d G:i:s', $timeasked);
						$OpenTime = (int) date_format($date, 'U');
						$daysago = seconds2human($CurrentTime - $OpenTime);
						
						//Display each comment as readonly for specific question
					if($_SESSION['user_id'] == $commentRow['StudentID']){
						echo "	
						<div class='name'>$commentRow[StudentName]<div class='date'>".$daysago." ago</div></div>
						<div class='comment'><div class='delete'><input type='submit' class='btn btn-danger btn-xs' id='$commentRow[CommentID]' name='deleteComment' 
						value='Delete Comment'></div><td>$commentRow[Content]</div><br>";
					}
					else {
						echo "	
						<div class='name'>$commentRow[StudentName]<div class='date'>".$daysago." ago</div></div>
						<div class='comment'><div class='delete'></div><td>$commentRow[Content]</div><br>";
					}
					} ?>
	<div align="center">
			<form name="cF" id="cF" action="api.php" method="post">
			<br>
				<div class='name'>Post a Quick Reply</div>
				<textarea class="form-control" name="comment" rows="5" id="content"></textarea>
				<?php echo
				"<input type='submit' class='btn btn-primary' id='addComment' name='addComment' value='Post Reply'>"
				?>
				<div id ="errorc"></div>
				<script>
                CKEDITOR.replace('comment');
            </script>
		</div>
		
		</form>
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
				if(func == "deleteComment"){
					var commentid = $(this).attr("id");
					var res = confirm("Are you sure you want to remove this comment?");
					if(res){
						var request = {f : func, params: [commentid]};
					}
					else {
						return false;
					}
				}
				else {
					var request = {f: func, params:  ['<?php echo $_GET['id']; ?>']};
				}
				if(func == "addComment"){
				for ( instance in CKEDITOR.instances ) {
           				 CKEDITOR.instances[instance].updateElement();
       		 	}
       		 	var content = document.forms["cF"]["comment"].value;
       		 	var stripped = $("#content").val().replace(/&nbsp;/g, " ");

       		 	stripped = stripped.replace("<p>", "");
       		 	stripped = stripped.replace("</p>", "");

				if(stripped.replace(/\s/g,"") == ""|| stripped == null){
					document.getElementById("errorc").innerHTML = "*You need to have some content for your comment"
					return false;
				}
				var date = '<?php echo date("Y-m-d  H:i:s", time()); ?>';
				//Use the action= property for ajax submission
				var fullname = '<?php echo $_SESSION['userfullname'];?>';
				var stnid = '<?php echo $_SESSION['user_id'];?>';
				//I changed this and now it works, before it was GETTING some other question ID for some reason
				var Qid = '<?php echo $_GET['id'];?>';
				var request = {f: func, params: [Qid, stnid, fullname, $("#content").val(), date]};
				}
				$.post("api.php", JSON.stringify(request), function() {
					if(func == "markResolved" || func == "markUnresolved" || func == "addComment"|| func == "deleteComment") location.reload(); 
					else window.location.replace("help.php");
				});
				return false;
			});
		});
	</script>
	<?php

		}
		else
		{
			$message = "wrong answer";
echo "<script type='text/javascript'>alert('$message');</script>";
		}
	?>
		<script type="text/javascript">
		/*$(function() {
			$('#cF').submit(function() {
				var form = $(this);
				var date = '<?php echo date("Y-m-d  H:i:s", time()); ?>';
				//Use the action= property for ajax submission
				var fullname = '<?php echo $_SESSION['userfullname'];?>'
				var stnid = '<?php echo $_SESSION['user_id'];?>'
				var url = form.attr('action');
				//I changed this and now it works, before it was GETTING some other question ID for some reason
				var Qid = '<?php echo $_GET['id'];?>'
				var func = form.data('function');
				var request = {f: func, params: [Qid, stnid, fullname, $("#content").val(), date]};
				//Post the serialized form.
				$.post(url, JSON.stringify(request), function(data) {
					//Handle submission.
					document.location.href = "displayQuestion.php?id=<?php echo $id; ?>";
				});
				
				
				return false;
			});
		});*/
	</script>
	<script type="text/javascript">
		window.onload = function () {
			$('.selectpicker').selectpicker();
		}
	</script>
</body>

</html>
