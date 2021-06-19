<?php


namespace App\Core;

use App\Core\Middlewares\BaseMiddleware;

/**
 * Class Controller
 * @package App\Core
 */
class Controller
{

	public string $layout = 'main';
	public string $action = '';

	/**
	 * @var BaseMiddleware[]
	 */
	protected array $middlewares = [];

	public function render($view, $params = []): bool|array|string
	{
		return Application::$app->view->renderView($view, $params);
	}

	public function setLayout($layout)
	{
		$this->layout = $layout;
	}

	public function registerMiddleware(BaseMiddleware $middlewares)
	{
		$this->middlewares[] = $middlewares;
	}

	/**
	 * @return BaseMiddleware[]
	 */
	public function getMiddlewares(): array
	{
		return $this->middlewares;
	}
}