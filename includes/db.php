<?php
class Database {
	private $db;
	public function query($stmt) {
		return $this->db->query($stmt);
	}
	public function prepare($stmt) {
		return $this->db->prepare($stmt);
	}
	
	public function lastInsertId() {
		return $this->db->lastInsertId();
	}

	public function __construct() {
		$this->db = new PDO('mysql:host=localhost;dbname=deco3801;charset=utf8', 'deco3801', 'hh2z2WG2q');
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
	}
}

$db = new Database();

abstract class PCRObject implements JsonSerializable {
	protected $db;
	private $id;
	protected $id_field;
	/* 
		uptodate determines if the object has been populated by a row.
		This allows set operations to occur without first retrieving data.
	*/
	protected $uptodate;
	protected $row;
	protected $table;
	protected $forceCreate;

	/* 
	 * PCRObject($id_field, $table, $data, $forceCreate)
	 * param id_field: The ID field of the database table.
	 * param table: The name of the database table.
	 * param data: A row containing the data. 
	 * param forceCreate: Should a new row be auto-created if an ID is 
	 * provided and a matching row is not found.
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
	
	
	private function updateRow($row) {
		$this->id = $row[$this->id_field];
		/* Update the row to match the latest set of data. */
		$update = "UPDATE $this->table SET ";
		foreach ($this->row as $key=>$value) {
			if ($key != $this->id_field)
				$update = $update . "$key = :$key,";
		}
		$update = rtrim($update, ",");
		$update = " WHERE $this->id_field = :$this->id_field;";
		
		try {
			$sth = $this->db->prepare($update);
			$sth->execute($this->row);
		} catch(PDOException $e) {
			return;
		}
	}
	
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
		
		$cols = rtrim($cols, ",");
		$vals = rtrim($vals, ",");
		
		try {
			$sth = $this->db->prepare("INSERT INTO $this->table ($cols) VALUES ($vals);");
			$sth->execute($this->row);

			$this->id = $this->row[$this->id_field] = $this->db->lastInsertId();
		} catch (PDOException $e) {
			//An error occured while inserting.
			return;
		}
	}
	/* 
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
					//Failed to select row. Set ID to null.
					$this->id = null;
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
			} else {
				$this->updateRow($row);
			}
			
			$this->row = $row;
			$this->uptodate = 1;
		}
	}
	
	public function getID() {
		$this->Update();
		return $this->id;
	}
	public function isValid() {
		$this->Update();
		return $this->id != null;
	}

	protected function delete() {
		$this->db->query("DELETE FROM $this->table WHERE $this->id_field = $id;");
	}
}

/**
 * Assignment Object
 * 
 * After population, contains the following rows:
 * 
 * (uint_16)		AssignmentID
 * (uint_16)		CourseID
 * (tinytext)		AssignmentName
 * (uint_8)			Weight
 * (uint_8)			SubmissionMethod
 * (uint_8)			ReviewsNeeded
 * (text)			AssignmentFiles
 * (text)			TestFiles
 * (timestamp)		OpenTime
 * (timestamp)		DueTime
 * (timestamp)		ReviewOpenTime
 * (timestamp)		ReviewsVisibleTime
 */
class Assignment extends PCRObject {
	public function __construct($data) {
		parent::__construct("AssignmentID", "Assignments", $data);
	}
	
	public function jsonSerialize() {
		parent::Update();
		return $this->row;
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
		return $this->row;
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
	
	public function uploadArchive() {
		if ($_FILES["file"]["error"] == 0) {
			$id = $this->getID();
			$file = $this->storage_dir . $_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"], $file);
			$zip = new ZipArchive;

			$path = pathinfo(realpath($file), PATHINFO_DIRNAME);

			$r = $zip->open($file);

			if ($r === TRUE) {
				$zip->extractTo($path);
				$zip->close();
				unlink($file);
			}
		}
	}
	
	public function uploadRepo($repo_url, $username, $password) {
		$id = $this->getID();
		exec("cd $this->storage_dir && git clone https://$username:$password@$repo_url .");
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

	public function addNewQuestion($title, $content, $stnid, $fullname){
		$sth = $this->db->prepare("INSERT INTO `deco3801`.`Question` (`StudentID`, `CourseID`, `StudentName`, `Title`, `Content`, `Status`) 
			VALUES ('".$stnid."', ".$this->getID().", '".$fullname."', '".$title."', '".$content."', '0');");
		$sth->execute(array($this->getID()));
		
	}
	
	public function helpEnabled() {
		$sth = $this->db->prepare("SELECT HelpEnabled FROM Course WHERE CourseID = ".$this->getID().";");
		$sth->execute(array($this->getID()));
		return $sth->fetchColumn();
	}

	public function getHelpCentreQuestions() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Question WHERE CourseID = ".$this->getID().";");
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
		$sth = $this->db->prepare("SELECT * FROM Assignments WHERE CourseID = ".$this->getID().";");
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
 * (text)			Title
 * (text)			Content
 * (int_8)			Status
 */
class Question extends PCRObject {
	public function __construct($data) {
		parent::__construct("QuestionID", "Question", $data);
	}
	
	public function getCommentsForQuestion($id){
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Comment WHERE QuestionID = ".$id.";");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Comment($file_row));
		}
		return $arr;
	}



	public function getQuestionContents($id){
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Question WHERE QuestionID = ".$id.";");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Question($file_row));
		}
		return $arr;
	}

	public function testRunFunction($stnid, $content){
		$sth = $this->db->prepare("INSERT INTO `deco3801`.`testtable` (`ID`, `content`) 
			VALUES ('".$stnid."', '".$content."');");
		$sth->execute(array($this->getID()));
		
	}
	/**
	* getComments returns an array of Comment objects for the given question,
	* which may be further manipulated.
	* @return an array of Question objects.
	*/
	public function getComments() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Comment WHERE QuestionID = ".$this->getID().";");
		$sth->execute(array($this->getID()));
		while ($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new Comment($file_row));
		}
		return $arr;
	}
	
	public function jsonSerialize() {
		parent::Update();
		return $this->row;
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
 * (text)			Content
 * (timestamp)		Time
 */	
class Comment extends PCRObject {
	public function __construct($data) {
		parent::__construct("CommentID", "Comment", $data);
	}
	

	
	public function jsonSerialize() {
		parent::Update();
		return $this->row;
	}
}