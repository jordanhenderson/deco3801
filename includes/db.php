<?php

class Database {
	private $db;
	public function query(string $stmt) {
		return $db->query($stmt);
	}
	public function prepare(string $stmt) {
		return $db->prepare($stmt);
	}

	public function __construct() {
		$db = new PDO('mysql:host=localhost;dbname=deco3801;charset=utf8', 'deco3801', 'hh2z2WG2q');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
	}
}

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
	protected function __construct($db, $id, $id_field, $table) {
		$this->db = $db;
		$this->id = $id;
		$this->id_field = $id_field;
		$this->table = $table;
		$uptodate = 0;
	}

	protected function __construct($db, $id_field, $table, array $row = array()) {
		$this->db = $db;
		$this->table = $table;
		$this->id_field = $id_field;

		$field_count = sizeof($row);
		if($field_count > 0) {
			$this->id = $row[0];
		} else {
			//Insert a new element.
			$sth = $db->prepare("INSERT INTO $table VALUES (" . rtrim(str_repeat("?,", $field_count)) . ");");
			$sth->execute($row);
			$id = $db->lastInsertId();
		}
		$uptodate = 1;
		$this->row = $row;
	}
	function delete() {
		$db->query("DELETE FROM $table WHERE $id_field = $id;");
	}	
}


class Assignment extends PCRObject {

}

class File extends PCRObject  {
	protected function __construct($db, $row) {
		parent::__construct($db, "FileID", "Files", $row);
	}

	static function getArray($rows) {
		$arr = array();
		foreach($rows as $row) {
			push_back($arr, new File($db, $row));
		}
		return $arr;
	}
	
	public function jsonSerialize() {
		return array($row["id"], $row["name"]);
	}
}

class Submission extends PCRObject {
	/**
	* getFiles returns an array of file objects which may be further manipulated.
	* @return an array of File objects.
	*/
	public function getFiles() {
		$sth = $db->prepare("SELECT * FROM Files WHERE SubmissionID = ?;");
		$sth->execute(array($id));
		return File.getArray();
	}
}


