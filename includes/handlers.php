<?php
if (session_id() == "") {
	session_start(); // start session if not already started
}

if (!isset($_SESSION['user_id'])) {
	header('Location: invalid.php');
	exit(); 
}

require_once("db.php");

/**
 * Needs Comment.
 */
class PCRHandler {
	/* Add additional API functions here.*/
	
	/**
	 * getFiles retrieves all files within a submission
	 * @param id the submission ID
	 * @return an array of files within a submission
	 */
	public function getFiles($id) {
		$submission = new Submission(array("SubmissionID"=>$id));
		return $submission->getFiles();
	}
	
	/**
	 * getCourse returns the current course.
	 * @return the course
	 */
	public function getCourse() {
		return new Course(array("CourseID"=>$_SESSION['course_id']));
	}
	
	/**
	 * Removes a question with the given id from the database.
	 * @param id of the question to remove
	 */
	public function removeQuestion($id) {
		$question = new Question(array("QuestionID"=>$id));
		$question->delete();
	}
	
	/**
	 * Marks the question specified by id as resolved.
	 * @param id of the question to mark as resolved
	 */
	public function markResolved($id) {
		$question = new Question(array("QuestionID"=>$id));
		$questionRow = &$question->getRow();
		$questionRow["Status"] = "1";
		$question->commit();
	}
	
	/**
	 * Marks the question specified by id as unresolved.
	 * @param id of the question to mark as unresolved
	 */
	public function markUnresolved($id) {
		$question = new Question(array("QuestionID"=>$id));
		$questionRow = &$question->getRow();
		$questionRow["Status"] = "0";
		$question->commit();
	}
	
	/**
	 * getAssignment returns an assignment with the provided id.
	 * @param id the assignment ID
	 * @return the assignment object
	 */
	public function getAssignment($id) {
		return new Assignment(array("AssignmentID"=>$id));
	}
	
	/**
	 * getSubmission returns a submission with the provided id.
	 * One submission per student per assignment.
	 * Only a submission for the current student can be returned 
	 * @param id the assignment id.
	 * @return the submission object
	 */
	public function getSubmission($id) {
		$assignment = new Assignment(array("AssignmentID"=>$id));
		return $assignment->getSubmission($_SESSION['user_id']);
	}
	
	/**
	 * getQuestion returns a question using the provided id.
	 * @param id the question ID
	 */
	public function getQuestion($id) {
		return new Question(array("QuestionID"=>$id));
	}
	
	/**
	 * addComment adds a comment to the database using the given parameters
	 */
	public function addComment($question_id, $studentid, $fullname, $content) {
		$question = $this->getQuestion($question_id);
		return $question->addComment($studentid, $fullname, $content);
	}
	
	/**
	 * getReview returns a review using the provided parameters
	 * @param id the review ID
	 * @return review object
	 */
	public function getReview($stnid, $id, $startIndex, $startLine, $annotationText, $text, $reviewID, $fileName) {
		return new Review(array("SubmissionID"=>'0',
								"Comments"=>$annotationText,
								"ReviewerID"=>$stnid,
								"ReviewID"=>$id,
								"startIndex"=>$startIndex,
								"startLine"=>$startLine,
								"fileName"=>$fileName,
								"text"=>$text));
	}
	
	/**
	 * uploadArchive uploads an archive to a submission
	 */
	public function uploadArchive() {
		$submission_id = isset($_POST["submission_id"]) ? $_POST["submission_id"] : null;
		$submission = new Submission(array("SubmissionID"=>$submission_id));
		if ($submission->isValid()) {
			$submission->uploadArchive();
			$submission->addFiles();
		}
	}
	
	/**
	 * uploadRepo uploads a repository to a submission
	 */
	public function uploadRepo($submission_id, $repo_url, $username, $password) {
		$submission = new Submission(array("SubmissionID"=>$submission_id));
		if ($submission->isValid()) {
			$submission->uploadRepo($repo_url, $username, $password);
			$submission->addFiles();
		}
	}
	
	/**
	 * Create a new assignment.
	 */
	public function createAssignment($AssignmentName, 
									$ReviewsNeeded, $ReviewsDue, $Weight, 
									$OpenTime, $DueTime) {
		$assignment = new Assignment(array("AssignmentID"=>$AssignmentID));
			
		$assignment['AssignmentName'] = $AssignmentName;
		$assignment['ReviewsNeeded'] = $ReviewsNeeded;
		$assignment['ReviewsDue'] = $ReviewsDue;
		$assignment['Weight'] = $Weight;
		$assignment['OpenTime'] = $OpenTime;
		$assignment['DueTime'] = $DueTime;

		$assignment->commit();
		return $assignment;
	}
	
	/**
	 * Update an existing assignment.
	 */
	public function updateAssignment($AssignmentID, $AssignmentName, 
									$ReviewsNeeded, $ReviewsDue, $Weight, 
									$OpenTime, $DueTime) {
		$assignment = new Assignment(array("AssignmentID"=>$AssignmentID));
			
		$assignment['AssignmentName'] = $AssignmentName;
		$assignment['ReviewsNeeded'] = $ReviewsNeeded;
		$assignment['ReviewsDue'] = $ReviewsDue;
		$assignment['Weight'] = $Weight;
		$assignment['OpenTime'] = $OpenTime;
		$assignment['DueTime'] = $DueTime;

		$assignment->commit();
		return $assignment;
	}
	
	/**
	 * Delete an assignment.
	 */
	public function deleteAssignment($AssignmentID) {
		$assignment = new Assignment(array("AssignmentID"=>$AssignmentID));
		$assignment->delete();
	}
}

/**
 * Needs Comment.
 */
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
	
	/**
	 * Needs Comment.
	 */
	public function handleRequest() {
		try {
			$response = null;
			$method = isset($this->request["f"]) ? $this->request["f"] : null;
			
			if ($method && method_exists($this->handler, $method)) {
				$fct = new ReflectionMethod($this->handler, $method);
				
				$params = isset($this->request["params"]) ? $this->request["params"] : array();
				if ($fct->getNumberOfRequiredParameters() == count($params))
					$response = call_user_func_array(array($this->handler, $method), $params);
			}
			if ($response) return json_encode(array("r"=> $response));
			else return "{}";
		} catch(Exception $e) {
			error_log($e);
			return "{}";
		}
	}
}