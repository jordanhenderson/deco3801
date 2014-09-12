<?php

if (session_id() == "") {
	session_start(); // start session if not already started
}

require_once("db.php");

class PCRHandler {
	/* Add additional API functions here. */
	/**
	* getFiles retrieves all files within a submission
	* @param id the submission ID
	* @returns an array of files within a submission
	*/
	public function getFiles($id) {
		$submission = new Submission(array("SubmissionID"=>$id));
		return $submission->getFiles();
	}

	/**
	* getCourse returns the current JSON serialized course.
	* @returns the course
	*/
	public function getCourse() {
		$course = new Course(array("CourseID"=>$_SESSION['course_id']));
		if($course->isValid()) {
			return $course;
		}
	}
	
	/**
	* getAssignment returns a JSON serialized assignment with the provided id.
	* @param id the assignment ID
	* @returns the assignment object
	*/
	public function getAssignment($id) {
		$assignment = new Assignment(array("AssignmentID"=>$id));
		if($assignment->isValid()) {
			return $assignment;
		}
	}
	
	/**
	* getSubmission returns a JSON serialized submission with the provided id.
	* One submission per student per assignment.
	* Only a submission for the current student can be returned 
	* @param id the assignment id.
	* @returns the submission object
	*/
	public function getSubmission($id) {
		$assignment = new Assignment(array("AssignmentID"=>$id));
		if($assignment->isValid()) {
			$submission = new Submission(array("AssignmentID"=>$id, "StudentID"=>$_SESSION['user_id']));
			return $submission;
		}
	}
	
	/**
	* getQuestion returns a JSON serialized question using the provided id.
	* @param id the question ID
	*/
	public function getQuestion($id) {
		$question = new Question(array("QuestionID"=>$id));
		if($question->isValid()) {
			return $question;
		}
	}
	
	/**
	* getComment returns a JSON serialized comment using the provided parameters
	* @param id the comment ID
	*/
	public function getComment($id, $content, $stnid, $fullname) {
		$comment = new Comment(array("StudentID"=>$stnid, "StudentName"=>$fullname, "QuestionID"=>$id, "Content"=>$content));
		if($comment->isValid()) {
			return $comment;
		}
	}

	/**
	* getComment returns a JSON serialized review using the provided parameters
	* @param id the review ID
	*/
    public function getReview($stnid, $id, $comments, $startoffset, $endoffset) {
        $review = new Review(array("StudentID"=>$stnid, "ReviewID"=>$id, "Comments"=>$comments, "StartOffset"=>$startoffset, "EndOffset"=>$endoffset, "SubmissionID"=>'0'));
        if($review->isValid()) {
            return " :) ";
        }
        return " :( ";
    }
	
	/**
	* uploadArchive uploads an archive to a submission
	*/
	public function uploadArchive() {
		$submission_id = isset($_POST["submission_id"]) ? $_POST["submission_id"] : null;
		$submission = new Submission(array("SubmissionID"=>$submission_id));
		if($submission->isValid()) {
			$submission->uploadArchive();
			$submission->addFiles();
		}
			
	}
	
		
	/**
	* uploadRepo uploads a repository to a submission
	*/
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

