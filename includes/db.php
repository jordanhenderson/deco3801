<?php
/**
 * Database contains a base database (PDO) and prepared query wrapper.
 * Add your custom database connection string and parameters to the constructor.
 */
require_once "config.php";
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
	/**
	 * uptodate determines if the object has been populated by a row.
	 * This allows set operations to occur without first retrieving data.
	 */
	protected $uptodate;
	/**
	 * forceCreate specifies if a row with the provided ID does not already exist.
	 */
	protected $forceCreate;
	/**
	 * PCRObject($id_field, $table, $data, $forceCreate)
	 * @param id_field: The ID field of the database table.
	 * @param table: The name of the database table.
	 * @param data: A row containing the data. 
	 * @param forceCreate: Should a new row be auto-created if an ID is 
	 *		  provided and a matching row is not found.
	 */
	protected function __construct($id_field, $table, $data, $forceCreate = 0) {
		$this->db = $GLOBALS["db"];
		$this->table = $table;
		$this->id_field = $id_field;
		$this->uptodate = 0;
		$this->forceCreate = $forceCreate;
		if (is_array($data)) {
			$this->row = $data;
			if (isset($data[$id_field]) && $data[$id_field] != null) {
				$this->id = $data[$id_field];
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
			if ($key != $this->id_field)
				$update .= "$key = :$key,";
		}
		$update = rtrim($update, ",");
		$update .= " WHERE $this->id_field = :$this->id_field;";

		try {
			$sth = $this->db->prepare($update);
			$sth->execute($this->row);
		} catch(PDOException $e) {
			return;
		}
	}
	
	/**
	* insertRow inserts a new entry into the database.
	* After calling this function, the object is considered synchronised
	*/
	private function insertRow() {
		//Insert a new row.
		$field_count = sizeof($this->row);
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

			$this->id = $this->row[$this->id_field] = $this->db->lastInsertId();
		} catch (PDOException $e) {
			//An error occured while inserting.
			return;
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
	public function Update($recursed=0) {
		if (!$this->uptodate) {
			//Populate the PCRObject.
			$sth = $this->db->prepare("SELECT * FROM $this->table WHERE $this->id_field = ?;");
			
			//Guarantee the id field has been provided.
			$row = null;
			if (!isset($this->id)) {
				//Insert a new row.
				$this->row[$this->id_field] = "NULL";
			} else {
				//Select an existing row - this may succeed or fail.
				$sth->execute(array($this->id));
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				if (!$row && !$this->forceCreate) {
					$this->row = null;
					return;
				}
			}
			
			//The provided ID did not return a row.
			if (!$row) {
				$this->insertRow();
				//Populate the freshly inserted row by calling Update again.
				//Only recurse once to prevent a loop - this might not be necessary.
				if (!$recursed) $this->Update(1);
				return;
			}
			
			$this->row = $row;
			$this->uptodate = 1;
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
	 * If forceCreate is not specified and no ID exists within the database, the provided
	 * object may have been created to access a specific entry (which does not exist)
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

	/**
	 * Delete the object within the database.
	 */
	public function delete() {
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
			$this->Update();
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
 * (text)			TestFiles
 * (timestamp)		OpenTime
 * (timestamp)		DueTime
 * (timestamp)		ReviewsDue
 */
class Assignment extends PCRObject {
	public function __construct($data) {
		parent::__construct("AssignmentID", "Assignments", $data);
	}
	
	public function jsonSerialize() {
		parent::Update();
		if (parent::isValid()) {
			return $this->row;
		}
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
	 * getIncompleteReviews returns the reviews that have not been completed
	 * for a given student.
	 * @return an array of Review objects.
	 */
	public function getIncompleteReviews($studentid) {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Review WHERE AssignmentID = ? AND ReviewerID = ? AND Submitted = 0;");
		$sth->execute(array($this->getID(), studentid));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Review($file_row));
		}
		return $arr;
	}
	
	/**
	 * Returns the submissions from this assignment. Should only ever be 1.
	 * @return an array of Submission objects.
	 */
	public function getSubmission($studentid) {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Submission WHERE AssignmentID = ? AND StudentID = ?;");
		$sth->execute(array($this->getID(), $studentid));
		return new Submission($sth->fetch(PDO::FETCH_ASSOC));
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
	public function __construct($data) {
		parent::__construct("FileID", "Files", $data);
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
	public function __construct($data) {
		parent::__construct("SubmissionID", "Submission", $data);
		$id = $this->getID();
		
		if ($this->isValid()) {
			$courseid = $_SESSION["course_id"];
			
			$assignmentid = $this->row["AssignmentID"];
			$this->storage_dir = "/var/www/upload/course_$courseid/assign_$assignmentid/submissions/$id/";
			if (!file_exists($this->storage_dir)) {
				mkdir($this->storage_dir, 0700, true);
			}
		}
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
	 */
	public function addFiles() {
		$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($this->storage_dir), 
						RecursiveIteratorIterator::SELF_FIRST );
		foreach ($iterator as $fileinfo) {
			if (!$fileinfo->isDir()) {
				$path = $iterator->getSubPathName();
				if (strpos($path, ".git") === false) {
					$f = new File(array("SubmissionID"=>$this->getID(), 
										"FileName"=>$iterator->getSubPathName()));
					$f->Update();
				}
			}
		}
	}
	
	/**
	 * This function can only be called from a file uploader script (passing 
	 * a file within the global $_FILES array).
	 * The provided archive will be extracted to the submission storage directory.
	 */
	public function uploadArchive() {
		if ($_FILES["file"]["error"] == 0) {
			$id = $this->getID();
			$file = $this->storage_dir . $_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"], $file);
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
		}
	}

	/**
	 * Check out a git repository to the assignment submission directory.
	 * @param repo_url the repository repo_url
	 * @param username the repository username
	 * @param password the repository password
	 */
	public function uploadRepo($repo_url, $username, $password) {
		$id = $this->getID();
		exec("cd $this->storage_dir && git clone https://$username:$password@$repo_url .");
	}
	
	/**
	 * getReviews returns an array of reviews for a submission.
	 * @return an array of reviews
	 */
	public function getReviews() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Review WHERE SubmissionID = ?;");
		//$sth->execute(array($this->getID()));
		// TODO: remove hardcoding
		$sth->execute(array('2'));
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
		//$sth->execute(array($this->getID()));
		// TODO: remove hardcoding
		$sth->execute(array('2'));
		$file_row = $sth->fetch(PDO::FETCH_ASSOC);
		$review = new Review($file_row);
		$review->delete();
	}
	
	/**
	 * addReview adds a review to the database
	 * @return the review that was added
	 */
	public function addReview($annotationText, $stnid, $id, $startIndex, $startLine, $fileName, $text, $reviewNum) {
		// $this->getID() is returning empty
		// TODO: unhardcode assignmentid and submissionid
		echo $annotationText . "::" . $stnid . "::" . $id . "::" . $startIndex . "::" . $startLine . "::" . $fileName . "::" . $text . "::";
		// Need to check if the review already exists and update if that's the case
		$review = new Review(array("AssignmentID"=>'3',
								"SubmissionID"=>'2',
								"Comments"=>$annotationText,
								"ReviewerID"=>$stnid,
								"startIndex"=>$startIndex,
								"startLine"=>$startLine,
								"fileName"=>$fileName,
								"text"=>$text,
								"reviewNum"=>$reviewNum));
		$review->commit();
		return $review;
	}
	
	/**
	 * editReview edits one of the reviews in the database
	 * @return the edited review
	 */
	public function editReview($prevComment, $annotationText) {
		$arr = array();
		$sth = $this->db->prepare("SELECT ReviewID FROM Review WHERE SubmissionID = ? AND Comments = '" . $prevComment . "';");
		//$sth->execute(array($this->getID()));
		// TODO: remove hardcoding
		$sth->execute(array('2'));
		$file_row = $sth->fetch(PDO::FETCH_ASSOC);
		$file_row["Comments"] = $annotationText;
		$review = new Review($file_row);
		$review->commit();
		return $review;
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
	public function __construct($data) {
		parent::__construct("CourseID", "Course", $data, 1);
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
	public function __construct($data) {
		parent::__construct("QuestionID", "Question", $data);
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
	public function __construct($data) {
		parent::__construct("ReviewID", "Review", $data, 1);
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
	public function __construct($data) {
		parent::__construct("CommentID", "Comment", $data);
	}
	
	public function jsonSerialize() {
		parent::Update();
		if (parent::isValid()) {
			return $this->row;
		}
	}
}