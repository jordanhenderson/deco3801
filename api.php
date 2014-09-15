<?php

require_once('includes/handlers.php');
require_once('includes/db.php');
$backend = new PCRBackend();
echo $backend->handleRequest();

$operation = new PCRHandler();
	$data = $_POST;
	//If its a question being added
	if(isset($data['question'])){
		$operation->storeNewQuestion($data['question'], $data['QContent'], $_SESSION['user_id'], $_SESSION['userfullname']);
				header('Location: help.php');
	}

	//If its a comment being added
	if(isset($data['comment'])){
		$operation->getComment($_SESSION['Questionid'], $data['comment'], $_SESSION['user_id'], $_SESSION['userfullname']);
		header('Location: displayQuestion.php?id='.$_SESSION['Questionid']);
	}	

	//These don't really send any data, they just remove/toggle values
	if(isset($_POST['action'])){
		switch ($_POST['action']) {
			case 'Remove Question':
			$operation->removeQuestionn($_SESSION['QuestionID']);
			break;
			case 'Remove Comment':
			//Todo
			break;
			case 'Mark Resolved':
			$operation->markResolved($_SESSION['QuestionID']);
			break;
			case 'Mark Unresolved':
			$operation->markUnresolved($_SESSION['QuestionID']);
			break;
			default: 
			break;
		}
	}
?>		