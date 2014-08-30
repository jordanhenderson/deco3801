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
		return $course;
	}
	
	public function getSubmission($id, $submission_output) {
		$assignment = new Assignment(array("AssignmentID"=>$id));
		if($assignment->isValid()) {
			$submission = new Submission(array("AssignmentID"=>$id, "StudentID"=>"1"));
			return $submission;
		}
	}
	
	public function uploadFile() {
		$submission_id = $_POST["submission_id"];
		if(!isset($submission_id)) {
			return;
		}

		if ($_FILES["file"]["error"] == 0) {
			
			$file = "storage/$submission_id/" . $_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"], $file);
			$zip = new ZipArchive;

			$path = pathinfo(realpath($file), PATHINFO_DIRNAME);

			$r = $zip->open($file);

			if($r === TRUE) {
				$zip->extractTo($path);
				$zip->close();
				unlink($file);
			}
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

