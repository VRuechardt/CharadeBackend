<?php

define("AUTH", "AUTH");
define("ERROR", "ERROR");
define("SUCCESS", "SUCCESS");
define("ROOMS", "ROOMS");
define("CREATE", "CREATE");
define("IN_ROOM", "IN_ROOM");
define("JOIN_ROOM", "JOIN_ROOM");
define("QUIT_ROOM", "QUIT_ROOM");
define("START", "START");
define("READY", "READY");

class Protocol {

	public $model;

	public function __construct($model) {
		$this->model = $model;
	}

	public function handle($message, $connection) {
		
		echo "handling:\n$message\n\n";

		$segments = explode("\n", $message);

		if($segments[0] == AUTH) {
			$this->auth($segments[1], $connection);
		} else if($segments[0] == ROOMS) {
			$this->rooms($connection);
		} else if($segments[0] == CREATE) {
			$this->create($connection, $segments[1]);
		} else if($segments[0] == IN_ROOM) {
			$this->inRoom($connection, $segments[1]);
		} else if($segments[0] == JOIN_ROOM) {
			$this->joinRoom($connection, $segments[1]);
		} else if($segments[0] == QUIT_ROOM) {
			$this->quitRoom($connection, $segments[1]);
		} else if($segments[0] == START) {
			$this->startGame($connection, $segments[1]);
		} else if($segments[0] == READY) {
			$this->playerReady($connection, $segments[1]);
		}
	}

	private function auth($token, $connection) {

		try {
			$user = new User($token, $connection);
			$this->model->authenticate($connection, $user);
			$connection->send(AUTH . "\n" . SUCCESS);
		} catch(Exception $e) {
			echo "wrong auth token\n";
			$connection->send(AUTH . "\n" . ERROR);
		}

	}

	private function rooms($connection) {

		$send = ROOMS;
		foreach($this->model->rooms as $key => $value) {
			$send .= "\n".$value->name;
		}
		$connection->send($send);

	}

	private function create($connection, $name) {

		$room =	$this->model->createRoom($name);
		if($room != NULL) {
			$room->join($this->model->userByConn($connection));
			$connection->send(CREATE . "\n" . SUCCESS . "\n" . $name);
		} else {
			$connection->send(CREATE . "\n" . ERROR);
		}
		

	}

	private function inRoom($connection, $name) {

		$send = IN_ROOM;

		if(isset($this->model->rooms[$name])) {
			$room = $this->model->rooms[$name];
			var_dump($room);
			foreach($room->members as $key => $value) {
				$send .= "\n".$value->name."\n".$value->id."\n".$value->ready;
			}
		}
		
		$connection->send($send);

	}

	private function joinRoom($connection, $name) {

		$send = IN_ROOM;

		if(isset($this->model->rooms[$name])) {
			$room = $this->model->rooms[$name];
			$room->join($this->model->userByConn($connection));
			foreach($room->members as $key => $value) {
				$send .= "\n".$value->name."\n".$value->id."\n".$value->ready;
			}
		}
		
		$connection->send($send);

	}

	private function quitRoom($connection, $name) {

		$room = $this->model->rooms[$name];
		if($room->leave($this->model->userByConn($connection)) == 1) {
			$this->model->removeRoom($name);
		};
		$connection->send(QUIT_ROOM . "\n" . SUCCESS);

	}

	private function playerReady($connection, $name) {

		$room = $this->model->rooms[$name];
		$user = $this->model->userByConn($connection);
		if($user->ready) {
			$user->ready = 0;
		} else {
			$user->ready = 1;
		}
		$room->broadcastMember();

	}

	private function startGame($connection, $name) {

		$room = $this->model->rooms[$name];
		$user = $this->model->userByConn($connection);
		if($room->admin()->id == $user->id) {
			if(!$room->start()) {
				$connection->send(START."\n".ERROR);
			}
		}

	}


}