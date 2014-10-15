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
		$crs = new Course(array("CourseID"=>$_SESSION['course_id']));
		if(!$crs->isValid()) 
			$crs->commit();
		return $crs;
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
	 * storeNewQuestion adds a new question to a course.
	 * @param title the question title
	 * @param content the question body content
	 * @param stnid the student ID
	 * @param fullname full name of the student
	 */
	public function storeNewQuestion($title, $content, $stnid, $fullname){
		$question = new Question(array(
									"StudentID" => $stnid,
									"CourseID" => $_SESSION["course_id"],
									"StudentName" => $fullname,
									"Title" => $title,
									"Content" => $content,
									"Status" => "0"
								));
		$question->commit();
		return $question;
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
	 * getSubmissionForReviewing returns a submission with the provided id.
	 * @param id the submission id.
	 * @return the submission object
	 */
	public function getSubmissionForReviewing($id) {
		return new Submission(array("SubmissionID"=>$id));
	}
	
	/**
	 * getStudent returns the current Student.
	 * @return the Student
	 */
	public function getStudent() {
		return new Review(array("StudentID"=>$_SESSION['user_id']));
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
		$question = PCRHandler::getQuestion($question_id);
		return $question->addComment($studentid, $fullname, $content);
	}
    
    /**
     * Function that is run when save is clicked. It will remove any deleted
     * reviews, update any edited ones, insert any new ones and ignore
     * unchanged ones
     */
    public function saveReviews($reviews) {
        $reviews = json_decode($reviews);
        foreach ($reviews as $review) {
            if ($review->status == 'd') { 
                $this->removeReview($review->Comments, $review->SubmissionID);
            } elseif ($review->status == 'e') { 
                $this->editReview($review->prevComment, $review->Comments, $review->SubmissionID);
            } elseif ($review->status == 'n') { 
                $this->addReview($review);
            }
        }
    }
    
	/**
	 * Removes a review with the given id from the database.
	 * @param submission id and comment of the review to remove
	 */
	public function removeReview($comment, $id) {
        // get submission
        $submission = new Submission(array("SubmissionID"=>$id));
        // call delete review for that submission
        return $submission->removeReview($comment);
	}
    
    /**
     * Edits a review with the given id from the database
     * @param submission id, comment and previous comment of the review to edit
     */
    public function editReview($prevComment, $annotationText, $id) {
        $submission = new Submission(array("SubmissionID"=>$id));
        return $submission->editReview($prevComment, $annotationText);
    }
    
    /**
	 * addReview adds a review to the database using the provided parameters
	 * @param id the review ID
	 * @return review object
	 */
	public function addReview($review) {
        // Get the submission for the student you are submitting a review for
        $submission = new Submission(array("SubmissionID"=>$review->SubmissionID));
        // Then add the review to the database
        return $submission->addReview($review->Comments, $_SESSION['user_id'], 
						$review->startIndex, $review->startLine, 
                        $review->fileName, $review->text);
	}
    
    /**
     * getReview returns an array of all the reviews for a given submission
     * @param the submission id
     * @return the list of arrays
     */
    public function getReviews($id) {
        // Get submission
        $submission = new Submission(array("SubmissionID"=>$id));
        // Get reviews for that submission
        return $submission->getReviews();
    }

    /**
     * getResults returns an array of all the reviews for a given submission
     * @param the submission id
     * @return the list of arrays
     */
    public function getResults($id) {
        // Get submission
        $submission = new Submission(array("SubmissionID"=>$id));
        // Get reviews for that submission
        return $submission->getResults();
    }
	
	/**
	 * uploadArchive uploads an archive to an assignment
	 */
	public function uploadArchive($assignment_id) {
		$submission = new Submission(array("AssignmentID"=>$assignment_id, "StudentID"=>$_SESSION['user_id']));
		if ($submission->isValid()) {
			if(!isset($_FILES["file"]) || $_FILES["file"]["error"] != 0) {
				$submission->delete();
				return;
			}
			
			$file = $submission->getStorageDir() . $_FILES["file"]["name"];
			
			if(is_uploaded_file($_FILES["file"]["tmp_name"]))
				move_uploaded_file($_FILES["file"]["tmp_name"], $file);
			else
				copy($_FILES["file"]["tmp_name"], $file);

			$zip = new ZipArchive;
			
			//Get the current path of the zip archive, open it.
			$path = pathinfo(realpath($file), PATHINFO_DIRNAME);
			$r = $zip->open($file);
			
			//Extract the zip archive to the assignment directory.
			if ($r === TRUE) {
				$zip->extractTo($path);
				$zip->close();
				unlink($file);
			}
			
			if($submission->addFiles() == 0) {
				$submission->delete();
				return;
			}
			// TODO Uncomment when ready for testing
			//$submission->testSubmission();
		}
		return $submission;
	}
	
	/**
	 * uploadRepo uploads a repository to an assignment
	 */
	public function uploadRepo($assignment_id, $repo_url, $username, $password) {
		$submission = new Submission(array("AssignmentID"=>$assignment_id, "StudentID"=>$_SESSION['user_id']));
		if ($submission->isValid()) {
			$dir = $submission->getStorageDir();
			exec("cd $dir && git clone https://$username:$password@$repo_url .");
			if($submission->addFiles() == 0) {
				//No files, failed?
				$submission->delete();
				return;
			}
			// TODO Uncomment when ready for testing
			//$submission->testSubmission();
		}
		return $submission;
	}
	
	/**
	 * Create a new assignment.
	 */
	public function changeAssignment($AssignmentID, $AssignmentName, $ReviewsNeeded, $ReviewsDue, $weight, $OpenTime, $DueTime){
		$assignment = new Assignment(array("AssignmentID" => $AssignmentID,
										   "AssignmentName" => $AssignmentName,
										   "CourseID" => $_SESSION['course_id'],
										   "ReviewsNeeded" => $ReviewsNeeded,
										   "ReviewsDue" => $ReviewsDue,
										   "Weight" => $weight,
										   "OpenTime" => $OpenTime,
										   "DueTime" => $DueTime));
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
	
	/*
     * Retrieves the file from the server and returns it to the calling page i.e. 
     * review_dev.php. 
	 */
	public function loadFile($courseID, $assignID, $subID, $fileName) {
		$assignment =  __DIR__ . "/storage/course_$courseID/assign_$assignID/submissions/$subID/" . $fileName;
		$handle = fopen($assignment, "r");
		$contents = fread($handle, filesize($assignment));
		$contents = str_replace('<', '&lt;', $contents);
		$contents = str_replace('>', '&gt;', $contents);
		fclose($handle);
		return $contents;
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
