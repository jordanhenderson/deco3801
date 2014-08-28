<?php

class PCRHandler {
	/* Add additional API functions here. */
	public function getFiles($id) {
		$submission = new Submission(array($id));
		return $submission->getFiles();
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
			return json_encode(call_user_func_array(array($this->handler, $this->request["f"]), $this->request["params"]));
		} catch(Exception $e) {
			return "{}";
		}
	}
}

