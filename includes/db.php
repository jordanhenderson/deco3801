<?php

class Database {
	private $db;
	public function query($stmt) {
		return $this->db->query($stmt);
	}
	public function prepare($stmt) {
		return $this->db->prepare($stmt);
	}

	public function __construct() {
		$this->db = new PDO('mysql:host=localhost;dbname=deco3801;charset=utf8', 'deco3801', 'hh2z2WG2q');
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
	}
}

$db = new Database();

abstract class PCRObject implements JsonSerializable {
	protected $db;
	protected $id;
	protected $id_field;
	/* 
		uptodate determines if the object has been populated by a row.
		This allows set operations to occur without first retrieving data.
	*/
	protected $uptodate;
	protected $row;
	protected $table;

	protected function __construct($id_field, $table, array $row = array()) {
		$this->db = $GLOBALS["db"];
		$this->table = $table;
		$this->id_field = $id_field;

		$field_count = sizeof($row);

		if($field_count == 1) {
			$this->id = $row["id"];
			$uptodate = 0;
		} else if($field_count == 0) {
			//Insert a new element.
			$sth = $db->prepare("INSERT INTO $table VALUES (" . rtrim(str_repeat("?,", $field_count)) . ");");
			$sth->execute($row);
			$this->id = $db->lastInsertId();
			$uptodate = 1;
		}
		
		$this->row = $row;
	}
	
	protected function getID() {
		return $this->id;
	}
	
	protected function delete() {
		$this->db->query("DELETE FROM $this->table WHERE $this->id_field = $id;");
	}
}


class Assignment extends PCRObject {
	public function __construct($row) {
		parent::__construct("AssignmentID", "Assignments", $row);
	}
	
	public function jsonSerialize() {
		return array($this->row["id"]);
	}
}

class File extends PCRObject  {
	public function __construct($row) {
		parent::__construct("FileID", "Files", $row);
	}

	public function jsonSerialize() {
		return array($this->row["id"], $this->row["name"]);
	}
}

class Submission extends PCRObject {
	public function __construct($row) {
		parent::__construct("SubmissionID", "Submission", $row);
	}
	
	/**
	* getFiles returns an array of file objects which may be further manipulated.
	* @return an array of File objects.
	*/
	public function getFiles() {
		$arr = array();
		$sth = $this->db->prepare("SELECT * FROM Files WHERE SubmissionID = ?;");
		$sth->execute(array($this->row["id"]));
		while($file_row = $sth->fetch(PDO::FETCH_ASSOC)) {
			push_back($arr, new File($db, $file_row));
		}
		return $arr;
	}
	
	public function jsonSerialize() {
		return array($this->row["id"]);
	}
}


