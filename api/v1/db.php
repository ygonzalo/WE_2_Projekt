<?php
class DB {
	private $host = 'localhost';
	private $username = 'weuser';
	private $password = 'weilbaum';
	private $database = 'watchedthatmovie';
	private $conn;

	function __construct() {
		$this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die();
		mysqli_set_charset($this->conn, "utf8");
	}

	public function escapeString($str) {
		return mysqli_real_escape_string($this->conn,$str);
	}
	
	public function preparedStmt($query) {
		$stmt = $this->conn->prepare($query);
		return $stmt;
	}

	public function allowTransactions() {
		$this->conn->autocommit(false);
	}
	
	public function commit(){
		$this->conn->commit();
	}

	public function rollback(){
		$this->conn->rollback();
	}

	public function getSession(){
		if (!isset($_SESSION)) {
			session_start();
		}
		$sess = array();
		if(isset($_SESSION['userID']))
		{
			$sess["userID"] = $_SESSION['userID'];
			$sess["name"] = $_SESSION['name'];
			$sess["email"] = $_SESSION['email'];
			if(isset($_SESSION['matches'])){
				$sess["matches"] = $_SESSION['matches'];
			}
		}
		else
		{
			$sess["userID"] = '';
			$sess["name"] = 'Guest';
			$sess["email"] = '';
		}
		return $sess;
	}
	
	public function changeSessionEmail($email) {
		if(isset($_SESSION['userID']))
		{
			$_SESSION['email'] = $email;
		}
	}

	public function changeSessionName($name) {
		if(isset($_SESSION['userID']))
		{
			$_SESSION['name'] = $name;
		}
	}

	public function destroySession(){
		if (!isset($_SESSION)) {
			session_start();
		}
		if(isset($_SESSION['userID']))
		{
			unset($_SESSION['userID']);
			unset($_SESSION['name']);
			unset($_SESSION['email']);
			$msg="Logged Out Successfully...";
		}
		else
		{
			$msg = "Not logged in...";
		}
		return $msg;
	}
}


?>
