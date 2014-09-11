<?php
//Formatting here is not done and code needs added parts.
session_start();
require_once 'includes/db.php';
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

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>
	<?php include 'header.php'; 
	$questions = $crs->getQuestion($id)->getQuestionContents($id);
				if (is_null($questions)) {
					echo 'no questions';
				} else {
					foreach ($questions as $question){
						$question = $question->jsonSerialize();
						$title = $question['Title'];
						$_SESSION['Status'] = $question['Status'];
						$timeasked = $question['Opendate'];
					}
				}
	$comments = $crs->getQuestion($question['QuestionID'])->getCommentsForQuestion($question['QuestionID']);
	?>
	<div class="container">
		<h1><?php echo $title; ?></h1>
		<form>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="specfiles"></label>
						<textarea name="content" readonly="readonly" rows="5" cols="110" id="content"><?php echo $question['Content']; ?></textarea>
					</div>
				</div>
				<div class="row">
				<div class="col-md-6">
					<label for="open">Asked On</label>
					<input size="24" type="text" readonly="readonly" value= "<?php echo $timeasked; echo ' by '.$question['StudentName']; ?>" class="form-control form_datetime" id="open" name="open">
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
						$comment = $comment->jsonSerialize();
						echo "
						<tr class='unresolved'>
						<td></td>
						<td><textarea name='comment' readonly='readonly' rows='5' cols='80' id='comment'>$comment[StudentName] Says: $comment[Content]</textarea></td>
						<td></td>";
					} ?>
				</div>
			<div align="center">
				<?php
					echo "
					<a class='btn btn-xl btn-warning' href='addComment.php?id=$question[QuestionID]' role='button'>Reply</a>
					";	
				//These go over three lines cause it spaces nicely
				if ($_SESSION['admin']) {
					echo "
					<a class='btn btn-xl btn-danger' href='deleteQuestion.php?id=$question[QuestionID]' role='button'>Remove Question</a>
					";
				}
				if($_SESSION['Status'] == 1){
					echo "
					<a class='btn btn-xl btn-danger' href='statusUpdate.php?id=$question[QuestionID]' role='button'>Mark Unesolved</a>
					";
				}
				else {
					echo "
					<a class='btn btn-xl btn-success' href='statusUpdate.php?id=$question[QuestionID]' role='button'>Mark Resolved</a>
					";
				}
				?>
			</div>
		</form>
		
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
</body>
</html>