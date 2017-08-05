<?php
class Lavender {
	private $events = array(
		'GET' => array(),
		'POST' => array(),
		'HEAD' => array(),
		'PUT' => array(),
		);
	
	public function route($methods = ["GET","POST"], $name = null, $action = null) {
		if ($name == null || $action == null) {
			return null;
		} 
		foreach ($methods as $method) {
			if ($this->check[$method] == false) {
				$this->events[$method][$name] == $action
			}
		}
	}

	public function check($name, $method){ return array_key_exists($name, $this->events[$method]); }

	public function emit($name, $requestType, $data = []) {
		if ($this->check($name, $requestType)) {
			$this->events[$requestType][$name]($data);
		}
	}

	public function useAction($name, $responder,  $domain = null) {
		include "actions/".$name."php";
		return new $name($responder, $domain);
	}

	public function redirectTo($name) {
		header('Location: ?r=/'.$name);
	}

	public function parseURL($url) {
		$data = explode('/', $url);
		unset($data[0]);
		$data = array_values($data);
		$event = $data[0];
		unset($data[0]);
		$params = array_values($data);
		return array($event, $params);
	}

	public function start($event = 'index', $params = []) {
		if(isset($_GET['r'])) {
			if (sizeof(explode('/',$_GET['r'])) == 0){
				$events = 'index';
				$params = array();
			} else {
				if ($this->check($this->parseURL($_GET['r'])[0])) {
					$parsedURL = $this->parseURL($_GET['r']);
					$event = $parsedURL[0];
					$params = $parsedURL[1];
				} else {
					$event = '404';
					$params = array();
				}
			}
		}
		$this->emit($event, Request::getRequest(),$data = $params);
	}
}



/**
Abstract classes for framework
**/
abstract class Action {
	public $domain = null;
	public $responder = null;

	public abstract function __construct($domain, $responder);
	public abstract function __invoke();
}

abstract class Responder implements View{
	public abstract function __invoke();
}

/**
Interfaces for framework
**/

interface View {
	function renderTemplate($name, $data = []);
	function renderHTML($text);
	function renderXML($data);
	function renderJSON($data);
	function renderCSV($data, $name = 'data');
}


/**
Helper Classes
**/
class Session {
	public static function start() { session_start(); }
	public static function get($name) { return $_SESSION[$name]; }
	public static function set($key, $value) { $_SESSION[$key] = $value; }
	public static function kill() { session_destroy(); }
}

class Request {
	public static function getRequest() { return $_SERVER['REQUEST_METHOD']; }
	public static function isGet() { return $_SERVER['REQUEST_METHOD'] == 'GET' ? 'true' : 'false';}
	public static function isPost() { return $_SERVER['REQUEST_METHOD'] == 'POST' ? 'true' : 'false';}
	public static function isHead() { return $_SERVER['REQUEST_METHOD'] == 'HEAD' ? 'true' : 'false';}
	public static function isPut() { return $_SERVER['REQUEST_METHOD'] == 'PUT' ? 'true' : 'false';}
}

