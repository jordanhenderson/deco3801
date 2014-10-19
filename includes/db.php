<?php
/**
 * Database contains a base database (PDO) and prepared query wrapper.
 * Add your custom database connection string and parameters to the constructor.
 */

require_once "config.php";
include "testingAPI.php";
error_reporting(E_ALL);

date_default_timezone_set('Australia/Brisbane');
class Database {
	private $db;
	/**
	 * query exeutes a single SQL query.
	 * @param stmt the SQL string to execute
	 * @return the SQL query object
	 */
	public function query($stmt) {
		return $this->db->query($stmt);
	}
	/**
	 * prepare returns a prepared PDO statement object
	 * @param stmt the SQL string to prepare
	 * @return the prepared statement object.
	 */
	public function prepare($stmt) {
		return $this->db->prepare($stmt);
	}
	
	/**
	 * lastInsertID returns the last inserted database row ID.
	 * @return the last inserted row ID.
	 */
	public function lastInsertId() {
		return $this->db->lastInsertId();
	}

	/**
	 * Construct a Database object. 
	 */
	public function __construct() {
		global $config;
		$this->db = new PDO('mysql:host=localhost;dbname=' . $config['dbname'] . ';charset=utf8', $config['dbuser'], $config['dbpass']);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
	}
}

$db = new Database();

/**
 * PCRObject provides a generic base class for objects represented within a relational database.
 * All functions relying on object data must first call parent::Update() to populate the row.
 * All derived classes must implement jsonSerialize() in order to serialize the object to JSON.
 */
abstract class PCRObject implements JsonSerializable {
	protected $db; //Database object reference
	private $id; //ID of the database object (within the objects' table)
	protected $id_field; //the field (column name) of the object's ID column (in which $id is stored)
	protected $table; //table is the name of the table which stores the object
	protected $row; //row holds the object
	protected $autocreate; //create the row if it doesn't exist
	/**
	 * uptodate determines if the object has been populated by a row.
	 * This allows set operations to occur without first retrieving data.
	 */
	protected $uptodate;
	/**
	 * PCRObject($id_field, $table, $data, $forceCreate)
	 * @param id_field: The ID field of the database table.
	 * @param table: The name of the database table.
	 * @param data: A row containing the data. 
	 * @param forceCreate: Should a new row be auto-created if an ID is 
	 *		  provided and a matching row is not found.
	 */
	protected function __construct($id_field, $table, $data, $autocreate) {
		$this->db = $GLOBALS["db"];
		$this->table = $table;
		$this->id_field = $id_field;
		$this->uptodate = 0;
		$this->autocreate = $autocreate;
		if (is_array($data)) {
			$this->row = $data;
			if (isset($data[$id_field]) && $data[$id_field] != null) {
				$this->Update();
			}
		}
	}
	
	/**
	* updateRow commits the object into the database, synchronising any changes.
	* @param row an array containing the individual key/value field pairs representing the object.
	*/
	private function updateRow($row) {
		$this->id = $row[$this->id_field];
		/* Update the row to match the latest set of data. */
		$update = "UPDATE $this->table SET ";
		foreach ($row as $key=>$value) {
			$update .= "$key = :$key,";
		}
		$update = rtrim($update, ",");
		$update .= " WHERE $this->id_field = :$this->id_field;";
		try {
			$sth = $this->db->prepare($update);
			$sth->execute($row);
			$this->row = $row;
		} catch(PDOException $e) {
			return;
		}
	}
	
	/**
	* insertRow inserts a new entry into the database.
	* After calling this function, the object is considered synchronised
	*/
	private function insertRow() {
		//Generate a prepared insert statement.
		$cols = "";
		$vals = "";
		foreach ($this->row as $key => $value) {
			$cols = $cols . "$key,";
			$vals = $vals . ":$key,";
		}
		
		//Trim commas
		$cols = rtrim($cols, ",");
		$vals = rtrim($vals, ",");
		//Execute the statement
		try {
			$sth = $this->db->prepare("INSERT INTO $this->table ($cols) VALUES ($vals);");
			$sth->execute($this->row);
			$id = $this->db->lastInsertId();
			$reqid = isset($this->row[$this->id_field]) ? $this->row[$this->id_field] : null;
			//Update the item's ID
			if($reqid != null && $id != $reqid) {
				$sth = $this->db->prepare("UPDATE $this->table SET $this->id_field = ? WHERE $this->id_field = ?;");
				$sth->execute(array($reqid, $id));
				$this->id = $reqid;
			} else {
				$this->id = $id;
			}
		} catch (PDOException $e) {
			//An error occured while inserting.
			return;
		}
	}

	/**
	 * Searches for a matching object given known fields.
	*/
	private function searchRow() {
		$search = "SELECT * FROM $this->table WHERE ";
		
		foreach ($this->row as $key=>$value) {
			$search .= " $key = :$key AND";
		}
		
		$search = rtrim($search, "AND") . ";";
		try {
			$sth = $this->db->prepare($search);
			$sth->execute($this->row);
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			if($row) {
				$this->row = $row;
				$this->uptodate = 1;
				$this->id = $row[$this->id_field];
			}
		} catch (PDOException $ex) {
			
		}
	}
	
	/**
	 * Update()
	 * Populates the database object if out of date. Must be called before
	 * every getter method in derived classes.
	 * 
	 * Most useful for objects created with just an ID.
	 * This function will INSERT a new row if the ID does not exist. 
	 * 
	 * Note: This function may fail if a new row is being inserted without
	 * valid constraints.
	 */
	public function Update($recursed = false) {
		if (!$this->uptodate) {
			//Populate the PCRObject.
			$sth = $this->db->prepare("SELECT * FROM $this->table WHERE $this->id_field = ?;");
			
			//Guarantee the id field has been provided.
			$row = null;
			$id = isset($this->id) ? $this->id : (isset($this->row[$this->id_field]) ? $this->row[$this->id_field] : null);
			if ($this->autocreate && $id == null) {
				//Insert a new row.
				$this->insertRow();
				//Repopulate the row with default values.
				if(!$recursed) $this->Update(true);
				$this->uptodate = 1;
			} else if ( $this->autocreate == false ) {
				//Autocreate not enabled, search for an existing row using what values we have.
				$this->searchRow();
			} else {
				//Select an existing row matching the ID - this may succeed or fail.
				$sth->execute(array($id));
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				
				if($row) {
					$changed = false;
					foreach ($this->row as $key => $value) {
						if($row[$key] !== $value) {
							$row[$key] = $value;
							$changed = true;
						}
					}
					if($changed) $this->updateRow($row);
					else $this->row = $row;
				}
				else {
					$this->insertRow();
				}
				
				$this->id = $id;
				$this->uptodate = 1;
			}
		}
	}
	
	/**
	 * getID updates the object and returns the objects' ID
	 * @return the row ID
	 */
	public function getID() {
		$this->Update();
		return $this->id;
	}
	
	/**
	 * isValid returns if the object is valid.
	 * An object is valid if an appropriate entry exists within the database.
	 * @return if the object is currently valid.
	 */
	public function isValid() {
		$this->Update();
		return $this->id != null && $this->uptodate == 1;
	}
	
	/**
	 * Return the objects' row by reference. This allows updating the row.
	 * @return a reference to the objects' row.
	 */
	public function &getRow() {
		$this->Update();
		return $this->row;
	}

	protected function Cleanup() {
		//override this function to provide custom cleanup logic.
	}
	
	/**
	 * Delete the object within the database.
	 */
	public function delete() {
		$this->Cleanup();
		if ($this->isValid()) {
			$this->db->query("DELETE FROM $this->table WHERE $this->id_field = $this->id;");
			$this->id = null;
		}
	}
	
	/**
	 * Pushes changes to object to database.
	 */
	public function commit() {
		if ($this->id != null) {
			$this->updateRow($this->row);
		} else {
			$this->insertRow();
		}
	}
}

class PCRBuilder {
	private $db;
	private $row;
	
	public function __construct($table) {
		$this->db = $GLOBALS["db"];
		$this->row = array();
		$qry = $this->db->query("SHOW COLUMNS FROM $table;");
		foreach ($qry as $row) {
			$def = $row["Default"];
			if ($def == "CURRENT_TIMESTAMP") {
				$def = "";
			}
			$this->row[$row["Field"]] = $def;
		}
	}
	
	/**
	 * Get the populated row.
	 */
	public function &getRow() {
		return $this->row;
	}
}

/**
 * Assignment Object
 * 
 * After population, contains the following rows:
 * 
 * (uint_16)		AssignmentID
 * (uint_16)		CourseID
 * (varchar(32))	AssignmentName
 * (uint_8)			Weight
 * (uint_8)			SubmissionMethod
 * (uint_8)			ReviewsNeeded
 * (timestamp)		OpenTime
 * (timestamp)		DueTime
 * (timestamp)		ReviewsDue
 * (text)			Language
 * (tinyint(1))		ReviewsAllocated
 * (tinyint(1))		ResubmitAllowed
 */
class Assignment extends PCRObject {
	private $ass_dir;
	public function __construct($data, $autocreate = true) {
		parent::__construct("AssignmentID", "Assignments", $data, $autocreate);
		$courseid = $_SESSION["course_id"];
		$assignid = $this->getID();
		$this->ass_dir = __DIR__ . "/../storage/course_$courseid/assign_$assignid/";
		if(!file_exists($this->ass_dir)) {
			mkdir($this->ass_dir, 0755, true);
			mkdir($this->ass_dir . "test", 0755, true);
			mkdir($this->ass_dir . "submissions", 0755, true);
		}
	}
	
	public function getDir() {
		return $this->ass_dir;
	}
	
	public function cleanTest() {
		$dir = $this->ass_dir . "/test/";
		if(file_exists($dir)) {
				if (PHP_OS === 'Windows')
				{
					exec("rd /s /q {$dir}/*");
				}
				else
				{
					exec("rm -rf {$dir}/*");
				}
			mkdir($dir, 0755, true);
		}
	}
	
	public function delete() {
		//Clean up all assignment files
		$id = $this->getID();
		$courseid = $_SESSION["course_id"];
		$dir = __DIR__ . "/../storage/course_$courseid/assign_$id/";
		$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it,
					 RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $file) {
			if ($file->getFilename() === '.' || $file->getFilename() === '..') {
				continue;
			}
			if ($file->isDir()){
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}
		rmdir($dir);
		parent::delete();
	}
	
	public function jsonSerialize() {
		parent::Update();
		if (parent::isValid()) {
			return $this->row;
		}
	}
	
	public function canResubmit() {
		return $this->row["ResubmitAllowed"] == "1" ? true : false;
	}
	
	/**
	 * Returns an array of all the submissions from this assignment.
	 * 
	 * @return an array of all submissions with the same AssignmentID as the
	 * object this was called from.
	 */
	public function getSubmissions() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Submission WHERE AssignmentID = ?;");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Submission($file_row));
		}
		return $arr;
	}
	
	/**
	 * getUnmarkedSubmissions returns the submissions that have not been
	 * reviewed by a given student, for a given assignment (and that were
	 * assigned to them).
	 * 
	 * @return an array of Submission objects.
	 */
	public function getUnmarkedSubmissions($studentid) {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Submission WHERE
			SubmissionID IN
				(SELECT Review.SubmissionID FROM Review WHERE ReviewerID = ? AND Submitted = 0) AND
			SubmissionID NOT IN
				(SELECT Review.SubmissionID FROM Review WHERE ReviewerID = ? AND Submitted = 1) AND
			AssignmentID = ?;");
		$sth->execute(array($studentid, $studentid, $this->getID()));
		
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Submission($file_row));
		}
		return $arr;
	}
	
	/**
	 * getMarkedSubmissions returns the submissions for a given student, for a
	 * given assignment, that have already been reviewed.
	 * 
	 * @return an array of Submission objects.
	 */
	public function getMarkedSubmissions($studentid) {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Submission WHERE
			SubmissionID IN
				(SELECT Review.SubmissionID FROM Review WHERE Submitted = 1 GROUP BY SubmissionID) AND
			AssignmentID = ? AND StudentID = ?;");
		$sth->execute(array($this->getID(), $studentid));
		
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Submission($file_row));
		}
		return $arr;
	}
	
	/**
	 * Returns the submissions from this assignment. Should only ever be 1.
	 * @return an array of Submission objects.
	 */
	public function getSubmission($studentid) {
		return new Submission(array("AssignmentID"=>$this->getID(), 
									"StudentID"=>$studentid), false);
	}
	
	/**
	 * Sets the assignments 'ReviewsAllocated' variable to 1 (true)
	 */
	public function setReviewsAllocated() {
		$this->row["ReviewsAllocated"] = "1";
		$this->commit();
	}
	
	/**
	 * Returns the Assignment Name for a given AssignmentID
	 * @return the assignment name
	 */
	 public function getAssignmentName() {
		$sth = $this->db->prepare("SELECT AssignmentName FROM Assignments WHERE AssignmentID = ?;");
		$sth->execute(array($this->getID()));
		return $sth->fetch(PDO::FETCH_ASSOC)['AssignmentName'];
	 }
}

/**
 * File Object
 * 
 * After population, contains the following rows:
 * 
 * (uint_16)		FileID
 * (uint_16)		SubmissionID
 * (text)			FileName
 */
class File extends PCRObject  {
	public function __construct($data, $autocreate = true) {
		parent::__construct("FileID", "Files", $data, $autocreate);
	}
	
	public function jsonSerialize() {
		parent::Update();
		if (parent::isValid()) {
			return $this->row;
		}
	}
}

/**
 * Submission Object
 * 
 * After population, contains the following rows:
 * 
 * (uint_16)		SubmissionID
 * (uint_16)		AssignmentID
 * (varchar(32))	StudentID
 * (text)			Results
 * (timestamp)		SubmitTime
 */
class Submission extends PCRObject {
	private $storage_dir;
	public function __construct($data, $autocreate = true) {
		parent::__construct("SubmissionID", "Submission", $data, $autocreate);
		$id = $this->getID();
		
		if ($this->isValid()) {
			$courseid = $_SESSION["course_id"];
			
			$assignmentid = $this->row["AssignmentID"];
			$this->storage_dir = __DIR__ . "/../storage/course_$courseid/assign_$assignmentid/submissions/$id/";
			if (!file_exists($this->storage_dir)) {
			 	mkdir($this->storage_dir, 0755, true);
			}
		}
	}
	
	public function getStorageDir() {
		return $this->storage_dir;
	}
	
	// TODO FIXME
	// All the following 'get' methods should be returning values, not arrays
	// with one value in them. I only changed getOwner, for now, but will fix
	// them all later.
	
	/**
	 * getOwner returns the owner of the submission
	 * @return the id of the owner
	 */
	public function getOwner() {
		$sth = $this->db->prepare("SELECT StudentID FROM Submission WHERE SubmissionID = ?;");
		$sth->execute(array($this->getID()));
		return $sth->fetch(PDO::FETCH_ASSOC)['StudentID'];
	}

	/**
	 * getResults returns the testing results of the submission
	 * @return the result of the tests
	 */
	public function getResults() {
		return $this->row["Results"];
	}
	
	/**
	 * getFiles returns an array of file objects which may be further manipulated.
	 * @return an array of File objects.
	 */
	public function getFiles() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Files WHERE SubmissionID = ?;");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new File($file_row));
		}
		return $arr;
	}

	/**
	 * addFiles iterates over the submissions' directory, recursively adding all
	 * files within as new database objects.
	 * @return amount of files added to submission
	 */
	public function addFiles() {
		$count = 0;
		$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($this->storage_dir), 
						RecursiveIteratorIterator::SELF_FIRST );
		foreach ($iterator as $fileinfo) {
			if (!$fileinfo->isDir()) {
				$path = $iterator->getSubPathName();
				if (strpos($path, ".git") === false) {
					$f = new File(array("SubmissionID"=>$this->getID(), 
										"FileName"=>$iterator->getSubPathName()));
					$count++;
					
				}
			}
		}
		return $count;
	}
	
	/**
	 * NOTE
	 * Please don't change where this is. It will break the review page.
	 *
	 * getReviews returns an array of reviews for a submission.
	 * @return an array of reviews
	 */
	public function getReviews() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Review WHERE SubmissionID = ?;");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Review($file_row));
		}
		return $arr;
	}
	
	/**
	 * getStudentsReviews returns the reviews for this submission, for a
	 * particular student.
	 * @return an array of reviews
	 */
	public function getStudentsReviews($stnid) {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Review WHERE ReviewerID = ? AND SubmissionID = ?");
		$sth->execute(array($stnid, $this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Review($file_row));
		}
		return $arr;
	}
	
	public function removeReview($comment) {
		// get the id of the review associated with $comment and the submission id
		// Create a new review out of it
		// Delete that review
		$arr = array();
		$sth = $this->db->prepare("SELECT ReviewID FROM Review WHERE SubmissionID = ? AND Comments = '" . $comment . "';");
		$sth->execute(array($this->getID()));
		// TODO: Fix hardcoded value
		//$sth->execute(array('2'));
		$file_row = $sth->fetch(PDO::FETCH_ASSOC);
		$review = new Review($file_row);
		$review->delete();
	}
	
	/**
	 * addReview adds a review to the database
	 * @return the review that was added
	 */
	public function addReview($annotationText, $stnid, $startIndex, $startLine, $fileName, $text, $submitted) {
		echo $annotationText . "::" . $stnid . "::" . $startIndex . "::" . $startLine . "::" . $fileName . "::" . $text . "--";
		// Need to check if the review already exists and update if that's the case
		$review = new Review(array("SubmissionID"=>$this->getID(),
								"Comments"=>$annotationText,
								"ReviewerID"=>$stnid,
								"startIndex"=>$startIndex,
								"startLine"=>$startLine,
								"fileName"=>$fileName,
								"text"=>$text,
								"Submitted"=>$submitted));
		$review->commit();
		return $review;
	}
	
	/**
	 * editReview edits one of the reviews in the database
	 * @return the edited review
	 */
	public function editReview($prevComment, $annotationText, $submitted) {
		$arr = array();
		$sth = $this->db->prepare("SELECT ReviewID FROM Review WHERE SubmissionID = ? AND Comments = '" . $prevComment . "';");
		$sth->execute(array($this->getID()));
		$file_row = $sth->fetch(PDO::FETCH_ASSOC);
		$file_row['Comments'] = $annotationText;
		$file_row['Submitted'] = $submitted;
		$review = new Review($file_row);
		$review->commit();
		return $review;
	}

	public function testSubmission($assignment_type, $test_file_location) {
		// Run appropriate tests
		switch ($assignment_type) {
			case 'bash':
				// TODO remove hardcoding filename 
				$tester = new bashTesting($test_file_location, $this->storage_dir . "tester.sh");
				$results = $tester->execute();

				// Update results in database
				$dbString = "";

				// Test resuts must be in string format to store in database
				foreach ($results as $value) {
					$dbString = $dbString . "," . $value;
				}

				// Removes the comma present at start of string
				$dbString = substr($dbString, 1);

				$this->row["Results"] = $dbString;
				$this->commit();
				break;
			case 'java':
				// Get assignment files location
				$assignment_file = $this->db->prepare("SELECT FileName FROM Files WHERE SubmissionID = ?;");
				$assignment_file->execute(array($this->getID()));
				$assignment_file = $assignment_file->fetch(PDO::FETCH_ASSOC)['FileName'];

				$tester = new javaTesting($this->storage_dir, $this->storage_dir, $assignment_file);
				$tester.compile();
				$tester.runJUnitTest();

				// Update results in database
				$this->row["Results"] = "pass";
				$this->commit(); 
				
				break;
			default:
				$this->row["Results"] = "No results";
				$this->commit();
				break;
		}
	}
	
	/**
	 * Returns the assignmentID of the submission.
	 */
	public function getAssignmentID() {
		// This returns empty :(
		parent::Update();
		return $this->row["AssignmentID"];
	}
	
	public function jsonSerialize() {
		parent::Update();
		return $this->row;
	}
}

/**
 * Course Object
 * 
 * After population, contains the following rows:
 * 
 * (varchar(32))	CourseID
 * (int_8)			HelpEnabled
 */
class Course extends PCRObject {
	public function __construct($data, $autocreate = true) {
		parent::__construct("CourseID", "Course", $data, $autocreate);
	}
	
	/**
	 * helpEnabled returns if the help center is enabled for the current course.
	 * @return boolean
	 */
	public function helpEnabled() {
		if ($this->isValid()) {
			return $this->row["HelpEnabled"];
		}
	}
	
	/**
	 *getHelpCentreQuestions returns an array of help centre questions for the course.
	 *@return an array containing each help centre question added to the course.
	 */
	public function getHelpCentreQuestions() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Question WHERE CourseID = ?;");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Question($file_row));
		}
		return $arr;
	}
	
	/**
	 * getAssignments returns an array of Assignment objects for the given
	 * course, which may be further manipulated.
	 * @return an array of Assignment objects.
	 */
	public function getAssignments() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Assignments WHERE CourseID = ?;");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Assignment($file_row));
		}
		return $arr;
	}
	
	public function jsonSerialize() {
		parent::Update();
		return $this->row;
	}
}

/**
 * Question Object
 * 
 * After population, contains the following rows:
 * 
 * (uint_16)		QuestionID
 * (varchar(32))	StudentID
 * (varchar(32))	CourseID
 * (varchar(32))	StudentName
 * (timestamp)		OpenDate
 * (text)			Title
 * (text)			Content
 * (int_8)			Status
 */
class Question extends PCRObject {
	public function __construct($data, $autocreate = true) {
		parent::__construct("QuestionID", "Question", $data, $autocreate);
	}
	
	/**
	 * markResolved sets the question status to resolved.
	 */
	public function markResolved() {
		$this->row["Status"] = "1";
		$this->commit();
	}
	
	/**
	 * markUnresolved sets the question status to resolved.
	 */
	public function markUnresolved() {
		$this->row["Status"] = "0";
		$this->commit();
	}

	/**
	 * getLastComment returns the last made comment object.
	 */
	public function getLastComment() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Comment WHERE QuestionID = ? ORDER BY postdate DESC limit 1;");
		$sth->execute(array($this->getID()));
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Comment($row));
		}
		return $arr;
	}

	/**
	 * getComments returns an array of Comment objects for the given question,
	 * which may be further manipulated.
	 * @return an array of Question objects.
	 */
	public function getComments() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Comment WHERE QuestionID = ?;");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Comment($file_row));
		}
		return $arr;
	}
	
	public function jsonSerialize() {
		parent::Update();
		if (parent::isValid()) {
			return $this->row;
		}
	}
	
	/**
	 * addComment adds a new Comment to the database.
	 * 
	 * @param stnid the student ID
	 * @param fullname the students full name
	 * @param content the content of the question
	 * @return the newly created Comment object
	 */
	public function addComment($stnid, $fullname, $content) {
		$comment = new Comment(array(
									"StudentID" => $stnid, 
									"QuestionID" => $this->getID(),
									"StudentName" => $fullname,
									"Content" => $content,
								));
		$comment->commit();
		return $comment;
	}
}

/**
 * Review Object
 * 
 * After population, contains the following rows:
 * 
 * (uint_16)		SubmissionID
 * (text)			Comments
 * (varchar(32))	ReviewerID
 * (uint_16)		ReviewID
 * (int(11))		startIndex
 * (int(11))		startLine
 * text				fileName
 * text				text
 */	
class Review extends PCRObject {
	public function __construct($data, $autocreate = true) {
		parent::__construct("ReviewID", "Review", $data, $autocreate);
	}
	
	/**
	 * getReviews returns an array of reviews available for a Student in a course
	 * @return an array of reviews
	 */
	public function getReviews() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Review INNER JOIN Submission ON Review.SubmissionID=Submission.SubmissionID INNER JOIN Assignments ON Submission.assignmentid=Assignments.assignmentid AND Assignments.ReviewsDue >= ? AND Review.ReviewerID = ? AND Assignments.CourseID = ? GROUP BY Review.SubmissionID");
		$sth->execute(array(date("Y-m-d H:i:s"), $_SESSION["user_id"], $_SESSION["course_id"]));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Review($file_row));
		}
		return $arr;
	}

	/**
	 * getReviews returns an array of reviews available for a Student in a course
	 * @return an array of reviews
	 */
	public function getFeedback() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Review INNER JOIN Submission ON Review.SubmissionID=Submission.SubmissionID INNER JOIN Assignments ON Submission.assignmentid=Assignments.assignmentid AND Assignments.ReviewsDue < ? AND Submission.StudentID = ? AND Assignments.CourseID = ? GROUP BY Review.SubmissionID");
		$sth->execute(array(date("Y-m-d H:i:s"), $_SESSION["user_id"], $_SESSION["course_id"]));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Review($file_row));
		}
		return $arr;
	}
	
	public function jsonSerialize() {
		parent::Update();
		if (parent::isValid()) {
			return $this->row;
		}
	}
}

/**
 * Comment Object
 * 
 * After population, contains the following rows:
 * 
 * (uint_16)		CommentID
 * (uint_16)		QuestionID
 * (varchar(32))	StudentID
 * (varchar(32))	StudentName
 * (text)			Content
 * (timestamp)		postdate
 */	
class Comment extends PCRObject {
	public function __construct($data, $autocreate = true) {
		parent::__construct("CommentID", "Comment", $data, $autocreate);
	}
	
	public function jsonSerialize() {
		parent::Update();
		if (parent::isValid()) {
			return $this->row;
		}
	}
}
