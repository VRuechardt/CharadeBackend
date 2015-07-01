<?php

include("Room.php");
include("User.php");

class Model {

	public $connections;
	public $users;
	public $rooms;

	public function __construct() {
		$this->connections = array();
		$this->rooms = array();
		$this->users = array();
	}

	public function addConnection($conn) {
		$this->connections[$conn->resourceId] = $conn;
		echo "conn: ".$conn->resourceId."\n";
	}

	public function removeConnection($conn) {
		foreach($this->rooms as $key => $value) {
			if($value->leave($this->userByConn($conn)->id) == 1) {
				unset($this->rooms[$key]);
			}
		}
		unset($this->connections[$conn->resourceId]);
		if(isset($this->users[$conn->resourceId])) {
			unset($this->users[$conn->resourceId]);
		}
		echo "quit: ".$conn->resourceId."\n";
	}

	public function authenticate($connection, $user) {

		$this->users[$connection->resourceId] = $user;

	}

	public function createRoom($name) {

		$taken = false;
		foreach($this->rooms as $key => $value) {
			if($value->name == $name) {
				$taken = true;
			}
		}
		if($taken) {
			return NULL;
		} else {
			$room = new Room($name);
			$this->rooms[$name] = $room;
			return $room;
		}
		
	}

	public function removeRoom($name) {
		echo "removing room $name\n";
		unset($this->rooms[$name]);
	}

	public function userByConn($conn) {
		return $this->users[$conn->resourceId];
	}

}