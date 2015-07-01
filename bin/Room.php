<?php

class Room {

	public $name;
	public $members;

	public function __construct($name) {
		$this->name = $name;
		$this->members = array();
	}

	public function join($user) {

		$key = array_search($user, $this->members);
		if($key === FALSE) {

			$user->ready = false;
			$send = IN_ROOM;
			foreach($this->members as $k => $v) {
				$send .= "\n" . $v->name . "\n" . $v->id."\n".$v->ready;
			}
			$send .= "\n" . $user->name . "\n" . $user->id."\n".$user->ready;
			foreach($this->members as $k => $v) {
				$v->connection->send($send);
			}

			array_push($this->members, $user);
		}

	}

	public function leave($user) {

		$key = -1;
		foreach($this->members as $k => $v) {
			if($v->id == $user) {
				$key = $k;
			}
		}

		if($key >= 0) {
			unset($this->members[$key]);
		}
		if(count($this->members) == 0) {
			return 1;
		}

		$this->broadcastMember();

		return 0;
		
	}

	public function broadcastMember() {
		
		$send = IN_ROOM;
		foreach($this->members as $k => $v) {
			$send .= "\n" . $v->name . "\n" . $v->id."\n".$v->ready;
		}
		foreach($this->members as $k => $v) {
			$v->connection->send($send);
		}

	}

	public function admin() {

		foreach($this->members as $k => $v) {
			return $v;
		}

	}

	public function start() {

		$ready = 1;
		$first = 1;
		foreach ($this->members as $k => $v) {
			if($first) {
				$first = 0;
			} else if(!$v->ready) {
				$ready = 0;
			}
		}

		if($ready) {
			foreach($this->members as $k => $v) {
				$v->connection->send(START);
			}
			return 1;
		} else {
			return 0;
		}

	}

}