<?php

class AuthenticationException extends Exception {}

class User {

	public $id;
	public $name;
	public $email;
	public $auth;
	public $connection;
	public $ready;

	public function __construct($auth, $connection) {

		global $link;
		$res = mysqli_query($link, "SELECT * FROM users WHERE auth = '$auth'");
		if(mysqli_num_rows($res) == 1) {

			$row = mysqli_fetch_object($res);

			$this->name = $row->name;
			$this->email = $row->email;
			$this->id = $row->id;
			$this->connection = $connection;
			$this->ready = 0;
			$this->auth = $auth;

		} else {
			throw new AuthenticationException();
		}

	}

}