<?php

class DB{
	private $server;
	private $username;
	private $database;
	private $pass;

	private $conn;
    private static $_instance;

	public static function getInstance(){
		if(!self::$_instance){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct(){
		$this->server = "sqlsrv:server = tcp:bxb-test.database.windows.net,1433";
		$this->database = "bxb";
		$this->username = "svc_BxB";
		$this->pass = "s3rvic3p@ss";


		try {
		    $this->conn = new PDO("$this->server; Database = $this->database", "$this->username", "{$this->pass}");
		    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e) {
		    print("Error connecting to SQL Server.");
		    die(print_r($e));
		}
	}

    // Magic method clone is empty to prevent duplication of connection
    private function __clone(){}

	public function getConnection(){
      return $this->conn;
    }

}
?>