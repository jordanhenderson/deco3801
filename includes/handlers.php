<?php

session_start();/*
if(!isset($_SESSION['user_id'])) {
	header('Location: invalid.php');
	exit();
}*/

include("db.php");

class PCRHandler {
	/* Add additional API functions here. */
	public function getFiles($id) {
		$submission = new Submission(array("SubmissionID"=>$id));
		return $submission->getFiles();
	}
	
	public function getCourse() {
		$course = new Course(array("CourseID"=>$_SESSION['course_id']));
		return $course;
	}
	
	public function getSubmission($id, $submission_output) {
		$assignment = new Assignment(array("AssignmentID"=>$id));
		if($assignment->isValid()) {
			$submission = new Submission(array("AssignmentID"=>$id, "StudentID"=>$_SESSION['user_id']));
			return $submission;
		}
	}
}

class PCRBackend {
	private $request;
	private $handler;
	public function __construct() {
		//Grab POST data
		$postData = file_get_contents('php://input');
		//Deserialize JSON request
		$this->request = json_decode($postData, true);
		$this->handler = new PCRHandler();
		
	}
	
	public function handleRequest() {
		try {
			$response = null;
			$method = isset($this->request["f"]) ? $this->request["f"] : null;
			
			if($method && method_exists($this->handler, $method)) {
				$fct = new ReflectionMethod($this->handler, $method);
				
				$params = isset($this->request["params"]) ? $this->request["params"] : array();
				if($fct->getNumberOfRequiredParameters() == count($params))
					$response = call_user_func_array(array($this->handler, $method), $params);
			}
			if($response) return json_encode(array("r"=> $response));
			else return "{}";
		} catch(Exception $e) {
			error_log($e);
			return "{}";
		}
	}
}

