<?php


namespace eligithub\phpmvc;


use JetBrains\PhpStorm\Pure;

class Request
{

	public function getPath()
	{
		$path = $_SERVER['REQUEST_URI'] ?? '/';
		$position = strpos($path, '?');
		if ($position === false) {
			return $path;
		}

		return substr($path, 0, $position);
	}

	public function method(): string
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	#[Pure] public function isGet(): bool
	{
		return ($this->method() === 'get');
	}

	#[Pure] public function isPost(): bool
	{
		return ($this->method() === 'post');
	}

	public function getBody(): array
	{
		$body = [];

		if ($this->isGet()) {
			foreach ($_GET as $key => $value) {
				$body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
			}
		}

		if ($this->isPost()) {
			if (empty($_POST)) {
				// If a form submits content-type of application/json, $_POST and $_REQUEST are not automatically filled.
				$_POST = array_merge($_POST, json_decode(file_get_contents('php://input'), true));
				$_REQUEST = array_merge($_POST, $_REQUEST);
			}
			foreach ($_POST as $key => $value) {
				$body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
			}
		}

		return $body;
	}
}