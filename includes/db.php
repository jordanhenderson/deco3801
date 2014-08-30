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

	/* 
	 * PCRObject($id_field, $table, $data, $is_new)
	 * param id_field: The ID field of the database table.
	 * param table: The name of the database table.
	 * param data: A row containing the data. 
	 * param createnew: Should a new row be auto-created if an ID is 
	 * provided and a matching row is not found.
	 */
	protected function __construct($id_field, $table, $data) {
		$this->db = $GLOBALS["db"];
		$this->table = $table;
		$this->id_field = $id_field;
		$this->uptodate = 0;
		
		if(is_array($data)) {
			$this->row = $data;
			if(isset($data[$id_field]) && $data[$id_field] != null) {
				$this->id = $data[$id_field];
			}
		}
	}
	
	
	private function updateRow($row) {
		$this->id = $row[$this->id_field];
		/* Update the row to match the latest set of data. */
		$update = "UPDATE $this->table SET ";
		foreach($this->row as $key=>$value) {
			if($key != $this->id_field)
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
		foreach($this->row as $key => $value) {
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
		if(!$this->uptodate) {
			
			//Populate the PCRObject.
			$sth = $this->db->prepare("SELECT * FROM $this->table WHERE $this->id_field = ?;");
			
			//Guarantee the id field has been provided.
			$row = null;
			if(!isset($this->id)) {
				//Insert a new row.
				$this->row[$this->id_field] = "NULL";
			} else {
				//Select an existing row - this may succeed or fail.
				$sth->execute(array($this->id));
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				if(!$row) {
					//Failed to select row. Set ID to null.
					$this->id = null;
					return;
				}
			}
			
			//The provided ID did not return a row.
			if(!$row) {
				$this->insertRow();
				//Populate the freshly inserted row by calling Update again.
				//Only recurse once to prevent a loop - this might not be necessary.
				if(!$recursed) $this->Update(1);
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


class Assignment extends PCRObject {
	public function __construct($data) {
		parent::__construct("AssignmentID", "Assignments", $data);
	}
	
	public function jsonSerialize() {
		parent::Update();
		return $this->row;
	}
}

class File extends PCRObject  {
	public function __construct($data) {
		parent::__construct("FileID", "Files", $data);
	}

	public function jsonSerialize() {
		parent::Update();
		return $this->row;
	}
}

class Submission extends PCRObject {
	private $storage_dir;
	public function __construct($data) {
		parent::__construct("SubmissionID", "Submission", $data);
		$id = $this->getID();
		$this->storage_dir = "storage/$id";
		if(!file_exists($this->storage_dir)) {
			mkdir($this->storage_dir, 0700, true);
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
		while($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			array_push($arr, new File($file_row));
		}
		return $arr;
	}
	
	public function addFiles() {
		$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($this->storage_dir), 
						RecursiveIteratorIterator::SELF_FIRST );
		foreach($iterator as $fileinfo) {
			if(!$fileinfo->isDir()) {
				$f = new File(array("SubmissionID"=>$this->getID(), 
									"FileName"=>$iterator->getSubPathName()));
				$f->Update();
			}
		}
	}
	
	public function uploadArchive() {
		print("Uploading file");
		if ($_FILES["file"]["error"] == 0) {
			$id = $this->getID();
			$file = "storage/$id/" . $_FILES["file"]["name"];
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
	
	public function uploadRepo($repo_url, $username, $password) {
		$id = $this->getID();
		
	}
	
	public function jsonSerialize() {
		parent::Update();
		return $this->row;
	}
}

class Course extends PCRObject {
	public function __construct($data) {
		parent::__construct("CourseID", "Course", $data);
	}
	public function jsonSerialize() {
		parent::Update();
		return $this->row;
	}

}


