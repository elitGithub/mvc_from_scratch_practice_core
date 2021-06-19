<?php


namespace App\Core\Middlewares;


use App\Core\Application;
use App\Core\Exceptions\ForbiddenException;

class AuthMiddleware extends BaseMiddleware
{

	public function __construct(public array $actions = [])
	{
	}

	/**
	 * @throws ForbiddenException
	 */
	public function execute()
	{
		if (Application::isGuest()) {
			if (empty($this->actions) || in_array(Application::$app?->getController()?->action, $this->actions)) {
				throw new ForbiddenException();
			}

		}
	}
}