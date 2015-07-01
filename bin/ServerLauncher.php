<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

$link = mysqli_connect("localhost", "root", "", "charade");

include("Model.php");
include("Protocol.php");

require dirname(__DIR__) . '/vendor/autoload.php';

class MyServer implements MessageComponentInterface {

	public $model;
	public $protocol;

	public function __construct() {
		$this->model = new Model();
		$this->protocol = new Protocol($this->model);
	}

	public function onOpen(ConnectionInterface $conn) {
		$this->model->addConnection($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    	$this->protocol->handle($msg, $from);
    }

    public function onClose(ConnectionInterface $conn) {
    	$this->model->removeConnection($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }

}



$server = IoServer::factory(
    new HttpServer(new WsServer(new MyServer())),
    8080
);

$server->run();