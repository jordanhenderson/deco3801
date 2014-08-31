<?php

session_start();
if(!isset($_SESSION['user_id'])) {
	header('Location: invalid.php');
	exit();
}

require_once("db.php");

class PCRHandler {
	/* Add additional API functions here. */
	public function getFiles($id) {
		$submission = new Submission(array("SubmissionID"=>$id));
		return $submission->getFiles();
	}
	
	public function getCourse() {
		$course = new Course(array("CourseID"=>$_SESSION['course_id']));
		if($course->isValid())
			return $course;
	}
	
	public function getSubmission($id) {
		$assignment = new Assignment(array("AssignmentID"=>$id));
		if($assignment->isValid()) {
			$submission = new Submission(array("AssignmentID"=>$id, "StudentID"=>$_SESSION['user_id']));
			return $submission;
		}
	}
	
	public function uploadArchive() {
		$submission_id = isset($_POST["submission_id"]) ? $_POST["submission_id"] : null;
		$submission = new Submission(array("SubmissionID"=>$submission_id));
		if($submission->isValid()) {
			$submission->uploadArchive();
			$submission->addFiles();
		}
		
	}
	
	public function uploadRepo($submission_id, $repo_url, $username, $password) {
		$submission = new Submission(array("SubmissionID"=>$submission_id));
		if($submission->isValid()) {
			$submission->uploadRepo($repo_url, $username, $password);
			$submission->addFiles();
		}
	}
	
	/*
	 * Create or update assignments.
	*/
	public function updateAssignment($assignment_id, $course_id, $assignment_name, 
									$reviews_needed, $review_opentime, $weight, 
									$opentime, $review_visibletime) {
		
		$assignment = new Assignment(array("AssignmentID"=>$assignment_id,
										   "CourseID"=>$course_id,
										   "AssignmentName"=>$assignment_name,
										   "ReviewsNeeded"=>$reviews_needed,
										   "ReviewOpenTime"=>$review_opentime,
										   "Weight"=>$weight,
										   "OpenTime"=>$opentime,
										   "ReviewsVisibleTime"=>$review_visibletime));
		if($assignment->isValid()) {
			return $assignment;
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

