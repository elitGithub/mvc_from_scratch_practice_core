<?php


namespace eligithub\phpmvc;


class Session
{
	protected const FLASH_KEY = 'flash_messages';

	public function __construct()
	{
		session_start();
		$flashMessages = $_SESSION[static::FLASH_KEY] ?? [];
		foreach ($flashMessages as $key => &$flashMessage) {
			$flashMessage['remove'] = true;
		}

		$_SESSION[static::FLASH_KEY] = $flashMessages;
	}

	public function setFlash($key, $message)
	{
		$_SESSION[static::FLASH_KEY][$key] = [
			'value'  => $message,
			'remove' => false,
		];
	}

	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	public function get($key)
	{
		return $_SESSION[$key] ?? false;
	}

	public function remove($key)
	{
		unset($_SESSION[$key]);
	}

	public function getFlash($key)
	{
		return $_SESSION[static::FLASH_KEY][$key]['value'] ?? false;
	}

	public function __destruct()
	{
		$flashMessages = $_SESSION[static::FLASH_KEY] ?? [];
		foreach ($flashMessages as $key => &$flashMessage) {
			if ($flashMessage['remove'] === true) {
				unset($flashMessages[$key]);
			}
		}

		$_SESSION[static::FLASH_KEY] = $flashMessages;
	}

}