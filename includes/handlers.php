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
		$submission = new Submission(array("SubmissionID" => $id));
		return $submission->getFiles();
	}
	
	/**
	 * getCourse returns the current course.
	 * @return the course
	 */
	public function getCourse() {
		$crs = new Course(array("CourseID" => $_SESSION['course_id']));
		if (!$crs->isValid()) 
			$crs->commit();
		return $crs;
	}
	
	/**
	 * Removes a question with the given id from the database.
	 * @param id of the question to remove
	 */
	public function removeQuestion($id) {
		$question = new Question(array("QuestionID" => $id));
		if (isset($_SESSION['admin'])) {
			$question->delete();
		}
	}

	/**
	 * Removes a comment with the given id from the database.
	 * @param id of the comment to remove
	 */
	public function deleteComment($id) {
		$comment = new Comment(array("CommentID" => $id));
		$commentRow = &$comment->getRow();
		if ($commentRow["StudentID"] == $_SESSION["user_id"]) {
			$comment->delete();
		}
		else{
			return null;
		}
	}
	
	/**
	 * Marks the question specified by id as resolved.
	 * @param id of the question to mark as resolved
	 */
	public function markResolved($id) {
		$question = new Question(array("QuestionID" => $id));
		$questionRow = &$question->getRow();
		$questionRow["Status"] = "1";
		$question->commit();
	}
	
	/**
	 * Marks the question specified by id as unresolved.
	 * @param id of the question to mark as unresolved
	 */
	public function markUnresolved($id) {
		$question = new Question(array("QuestionID" => $id));
		$questionRow = &$question->getRow();
		$questionRow["Status"] = "0";
		$question->commit();
	}
	
	/**
	 * Marks the question specified by id as unresolved.
	 * @param status of the help centre currently
	 */
	public function toggleHelp($status) {
		$crs = new Course(array("CourseID" => $_SESSION['course_id']));
		$help = &$crs->getRow();
		if (isset($_SESSION['admin'])) {
		if ($status == 0) {
			$help['HelpEnabled'] = "1";
			$crs->commit();
		} else {
			$help['HelpEnabled'] = "0";
			$crs->commit();
		}
	}
	}
	
	/**
	 * storeNewQuestion adds a new question to a course.
	 * @param title the question title
	 * @param content the question body content
	 * @param stnid the student ID
	 * @param fullname full name of the student
	 */
	public function storeNewQuestion($title, $content, $stnid, $fullname, $postdate) {
		if (!trim($content) || !trim($title)) {
			return null;
		} else {
			$question = new Question(array(
						"StudentID" => $stnid,
						"CourseID" => $_SESSION["course_id"],
						"StudentName" => $fullname,
						"Opendate" => $postdate,
						"Title" => $title,
						"Content" => $content,
						"Status" => "0"
					));
			$question->commit();
			return $question;
		}
	}
	
	/**
	 * getAssignment returns an assignment with the provided id.
	 * @param id the assignment ID
	 * @return the assignment object
	 */
	public function getAssignment($id) {
		return new Assignment(array("AssignmentID" => $id));
	}
	
	/**
	 * getSubmission returns a submission with the provided id.
	 * One submission per student per assignment.
	 * Only a submission for the current student can be returned 
	 * @param id the assignment id.
	 * @return the submission object
	 */
	public function getSubmission($id) {
		$assignment = new Assignment(array("AssignmentID" => $id));
		return $assignment->getSubmission($_SESSION['user_id']);
	}
	
	/**
	 * getSubmissionForReviewing returns a submission with the provided id.
	 * @param id the submission id.
	 * @return the submission object
	 */
	public function getSubmissionForReviewing($id) {
		return new Submission(array("SubmissionID" => $id), false);
	}
	
	/**
	 * getStudent returns the current Student.
	 * @return the Student
	 */
	public function getStudent() {
		return new Review(array("StudentID" => $_SESSION['user_id']));
	}
	
	/**
	 * getQuestion returns a question using the provided id.
	 * @param id the question ID
	 */
	public function getQuestion($id) {
		return new Question(array("QuestionID" => $id));
	}
	
	/**
	 * addComment adds a comment to the database using the given parameters
	 */
	public function addComment($question_id, $studentid, $fullname, $content, $date) {
		$question = PCRHandler::getQuestion($question_id);
		return $question->addComment($studentid, $fullname, $content, $date);
	}
	
	/**
	 * assignReviews distributes reviews to each student who made a submission,
	 * up to the amount of reviews required, or the amount of submissions made
	 * minus 1.
	 * @param assignment ID
	 */
	public function assignReviews($assign_id) {
		$asg = new Assignment(array("AssignmentID" => $assign_id));
		
		if (!$asg->isValid()) return;
		
		$submissions = $asg->getSubmissions();
		$asg = $asg->getRow();
		
		//Abort if any existing reviews for assignment
		
		$reviewnum = $asg['ReviewsNeeded'];
		
		// for each submission
		for ($i = 0; $i < count($submissions); ++$i) {
			// the student who made the submission must mark 'reviewnum' submissions.
			for ($j = 0; $j < $reviewnum; ++$j) {
				// Make review row for student/submission
				$index = ($i + $j + 1) % count($submissions);
				$reviewerID = $submissions[$index]->getOwner();
				if ($reviewerID != $submissions[$i]->getOwner()) {
					$exist_review = new Review(array("ReviewerID" => $reviewerID, "SubmissionID" => $submissions[$i]->getID()), false);
					if (!$exist_review->isValid()) {
						$submissions[$i]->addReview("", $reviewerID, 0, 0, null, "", 0);
					}
				}
			}
		}
	}
	
	/**
	 * Function that is run when save is clicked. It will remove any deleted
	 * reviews, update any edited ones, insert any new ones and ignore
	 * unchanged ones
	 * @param an array of review objects
	 */
	public function saveReviews($reviews) {
		foreach ($reviews as $review) {
			if ($review["status"] == 'd') {
				$this->removeReview($review["ReviewID"], $review["SubmissionID"]);
			} elseif ($review["status"] == 'e') {
				$this->editReview($review["startLine"], $review["startIndex"], $review["ReviewID"], $review["Comments"], $review["SubmissionID"], 0);
			} elseif ($review["status"] == 'n') {
				$this->addReview($review, 0);
			} elseif ($review["status"] == 'o') {
				$this->editReview($review["startLine"], $review["startIndex"], $review["ReviewID"], $review["Comments"], $review["SubmissionID"], 1);
			}
		}
	}
	
	/**
	 * Function that is run when submit is clicked. It will remove any deleted
	 * reviews, update any edited ones, insert any new ones and ignore
	 * unchanged ones. It will then remove that users access to reviewing the 
	 * submission
	 * @param an array of review objects
	 */
	public function submitReviews($reviews) {
		foreach ($reviews as $review) {
			if ($review["status"] == 'd') {
				$this->removeReview($review["ReviewID"], $review["SubmissionID"]);
			} elseif ($review["status"] == 'e') {
				$this->editReview($review["startLine"], $review["startIndex"], $review["ReviewID"], $review["Comments"], $review["SubmissionID"], 1);
			} elseif ($review["status"] == 'n') {
				$this->addReview($review, 1);
			} elseif ($review["status"] == 'o') {
				$this->editReview($review["startLine"], $review["startIndex"], $review["ReviewID"], $review["Comments"], $review["SubmissionID"], 1);
			}
		}
		$submission = new Submission(array("SubmissionID"=>$id), false);
		return $submission->removeAccess();
	}
	
	/**
	 * Removes a review with the given id from the database.
	 * @param submission id and comment of the review to remove
	 */
	public function removeReview($reviewID, $id) {
		// get submission
		$submission = new Submission(array("SubmissionID" => $id), false);
		// call delete review for that submission
		return $submission->removeReview($reviewID);
	}
	
	/**
	 * Edits a review with the given id from the database
	 * @param submission id, comment and previous comment of the review to edit
	 */
	public function editReview($startLine, $startIndex, $reviewID, $annotationText, $id, $submitted) {
		$submission = new Submission(array("SubmissionID" => $id), false);
		return $submission->editReview($startLine, $startIndex, $reviewID, $annotationText, $submitted);
	}
	
	/**
	 * addReview adds a review to the database using the provided parameters
	 * @param id the review ID
	 * @return review object
	 */
	public function addReview($review, $submitted) {
		// Get the submission for the student you are submitting a review for
		$submission = new Submission(array("SubmissionID" => $review["SubmissionID"]), false);
		if (!$submission->isValid()) return;
		// Then add the review to the database
		return $submission->addReview($review["Comments"], $_SESSION['user_id'], 
						$review["startIndex"], $review["startLine"], 
						$review["FileID"], $review["text"], $submitted);
	}
	
	/**
	 * getReview returns an array of all the reviews for a given submission
	 * @param the submission id
	 * @return the list of arrays
	 */
	public function getReviews($id) {
		// Get submission
		$submission = new Submission(array("SubmissionID" => $id), false);
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
		$submission = new Submission(array("SubmissionID" => $id));
		// Get reviews for that submission
		return $submission->getResults();
	}
	
	/**
	 * TESTING FUNCTION ONLY - FOR TUTORS CONVENIENCE AND DEMO
	 * Sets the assignment with the provided ID to have the following dates:
	 * 
	 * OPEN:		5 November 2014, 11am
	 * DUE:			30 November 2014, 11am
	 * REVIEWSDUE:	31 November 2014, 11am
	 */
	public function makeopen($assignment_id) {
		$assignment = new Assignment(array("AssignmentID" => $assignment_id,
										   "OpenTime" => "2014-11-5 11:00:00",
										   "DueTime" => "2014-11-30 11:00:00",
										   "ReviewsDue" => "2014-11-31 11:00:00"));
		if (!$assignment->isValid()) {
			return;
		}
		$assignment->commit();
	}
	
	/**
	 * TESTING FUNCTION ONLY - FOR TUTORS CONVENIENCE AND DEMO
	 * Sets the assignment with the provided ID to have the following dates:
	 * 
	 * OPEN:		5 November 2014, 11am
	 * DUE:			6 November 2014, 11am
	 * REVIEWSDUE:	31 November 2014, 11am
	 */
	public function makedue($assignment_id) {
		$assignment = new Assignment(array("AssignmentID" => $assignment_id,
										   "OpenTime" => "2014-11-5 11:00:00",
										   "DueTime" => "2014-11-6 11:00:00",
										   "ReviewsDue" => "2014-11-31 11:00:00"));
		if (!$assignment->isValid()) {
			return;
		}
		$assignment->commit();
	}
	
	/**
	 * TESTING FUNCTION ONLY - FOR TUTORS CONVENIENCE AND DEMO
	 * Sets the assignment with the provided ID to have the following dates:
	 * 
	 * OPEN:		5 November 2014, 11am
	 * DUE:			6 November 2014, 11am
	 * REVIEWSDUE:	7 November 2014, 11am
	 */
	public function makereviewsdue($assignment_id) {
		$assignment = new Assignment(array("AssignmentID" => $assignment_id,
										   "OpenTime" => "2014-11-5 11:00:00",
										   "DueTime" => "2014-11-6 11:00:00",
										   "ReviewsDue" => "2014-11-7 11:00:00"));
		if (!$assignment->isValid()) {
			return;
		}
		$assignment->commit();
	}
	
	/**
	 * uploadTest uploads tests for an assignment.
	 * @param assignment ID
	*/
	public function uploadTest($assignment_id) {
		$assignment = new Assignment(array("AssignmentID" => $assignment_id));
		if (!$assignment->isValid()) return;
		if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != 0) {
			return;
		}
		
		//Clear existing test files
		$assignment->cleanTest();
		
		$file = $assignment->getDir() . "/test/" . $_FILES["file"]["name"];
		
		if (is_uploaded_file($_FILES["file"]["tmp_name"]))
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
			
			//Set executable flag on run.sh.
			chdir($assignment->getDir() . "/test/");
			if (!file_exists("run.sh")) {
				//Test invalid, no run.sh!
				$assignment->cleanTest();
			} else {
				chmod("run.sh", 0750);
			}
		}
	}
	
	/**
	 * uploadArchive uploads an archive to an assignment
	 * @param assignment ID
	 */
	public function uploadArchive($assignment_id) {
		$assignment = new Assignment(array("AssignmentID" => $assignment_id));
		if (!$assignment->isValid()) return;
		
		//Look for an existing submission - return if resubmission not allowed.
		$oldsubmission = $assignment->getSubmission($_SESSION['user_id']);
		if (!$assignment->canResubmit() && $oldsubmission->isValid()) return;
		$oldsubmission->delete();
		
		$submission = new Submission(array("AssignmentID" => $assignment_id, "StudentID" => $_SESSION['user_id'], "Results" => ""));
		
		if ($submission->isValid()) {
			if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != 0) {
				$submission->delete();
				return;
			}
			
			$file = $submission->getStorageDir() . $_FILES["file"]["name"];
			
			if (is_uploaded_file($_FILES["file"]["tmp_name"]))
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
			
			if ($submission->addFiles() == 0) {
				$submission->delete();
				return;
			}

			$assign = &$assignment->getRow();
			$test_file_location = $assignment->getDir() . "test/";
			$submission->testSubmission($test_file_location);
					
		}
		return $submission;
	}
	
	/**
	 * uploadRepo uploads a repository to an assignment
	 */
	public function uploadRepo($assignment_id, $repo_url, $username, $password) {
		$assignment = new Assignment(array("AssignmentID" => $assignment_id));
		if (!$assignment->isValid()) return;
		
		//Look for an existing submission - return if resubmission not allowed.
		$oldsubmission = $assignment->getSubmission($_SESSION['user_id']);
		if (!$assignment->canResubmit() && $oldsubmission->isValid()) return;
		
		$oldsubmission->delete();
		print_r($oldsubmission);
		
		$submission = new Submission(array("AssignmentID" => $assignment_id, "StudentID" => $_SESSION['user_id']));
		if ($submission->isValid()) {
		
			$dir = $submission->getStorageDir();
			exec("cd $dir && git clone https://$username:$password@$repo_url .");
			
			if ($submission->addFiles() == 0) {
				//No files, failed?
				$submission->delete();
				return;
			}

			$test_file_location = $assignment->getDir() . "test/";
			$submission->testSubmission($test_file_location);
		}
		return $submission;
	}
	
	/**
	 * Create a new assignment.
	 */
	public function changeAssignment($AssignmentID, $AssignmentName, $ReviewsNeeded, $ReviewsDue, $weight, $OpenTime, $DueTime, $ResubmitAllowed, $NumberTests) {
		$assignment = new Assignment(array("AssignmentID" => $AssignmentID,
										   "AssignmentName" => $AssignmentName,
										   "CourseID" => $_SESSION['course_id'],
										   "ReviewsNeeded" => $ReviewsNeeded,
										   "ReviewsDue" => $ReviewsDue,
										   "Weight" => $weight,
										   "OpenTime" => $OpenTime,
										   "DueTime" => $DueTime,
										   "ResubmitAllowed" => $ResubmitAllowed,
										   "NumberTests" => $NumberTests));
		$assignment->commit();
		return $assignment;
	}
	
	/**
	 * Delete an assignment.
	 * @param assignment ID
	 */
	public function deleteAssignment($AssignmentID) {
		$assignment = new Assignment(array("AssignmentID" => $AssignmentID));
		$assignment->delete();
	}
	
	/**
	 * Retrieves the file from the server and returns it to the calling page i.e. 
	 * review_dev.php
	 * @param File ID
	 */
	public function loadFile($fileID) {
		$file = new File(array("FileID" => $fileID));
		if (!$file->isValid()) return "";
		$file_row = $file->getRow();

		$submission = new Submission(array("SubmissionID" => $file_row["SubmissionID"]));
		if (!$submission->isValid()) return ""; //invalid submission ID?

		if (!isset($_SESSION['admin'])) {
			//Check to ensure we have access to the file
			$submission_row = $submission->getRow();
			if ($_SESSION['user_id'] !== $submission_row['StudentID']) {
				//Check to ensure the student cannot review this submission
				$review = new Review(array("ReviewerID" => $_SESSION['user_id'], "SubmissionID" => $submission->getID()), false);
				if (!$review->isValid()) return ""; //user should not be able to access file!
			}
		}

		//$assignment = __DIR__ . "/../storage/course_$courseID/assign_$assignmentid/submissions/$submissionID/" . $fileName;
		$fileName = $submission->getStorageDir() . $file_row["FileName"];
		$handle = fopen($fileName, "r");
		$contents = fread($handle, filesize($fileName));
		$contents = str_replace('<', '&lt;', $contents);
		$contents = str_replace('>', '&gt;', $contents);
		fclose($handle);
		return $contents;
	}
}

/**
 * 
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
	 * handleRequest deas with requests that specify a particular method to be
	 * run, with particular parameters.
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
			if ($response) return json_encode(array("r" => $response));
			else return "{}";
		} catch(Exception $e) {
			error_log($e);
			return "{}";
		}
	}
}
