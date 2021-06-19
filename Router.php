<?php

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\SiteController;
use App\Core\Exceptions\NotFoundException;
use JetBrains\PhpStorm\Pure;

/**
 * Class Router
 * @package App\Core
 */
class Router
{
	protected Application $app;

	/**
	 * @var array
	 */
	protected array $routes = [];

	/**
	 * Router constructor.
	 *
	 * @param  Request  $request
	 * @param  Response  $response
	 */
	#[Pure] public function __construct(public Request $request, public Response $response)
	{
		$this->app = Application::getApp();
	}

	/**
	 * @param $path
	 * @param $callback
	 */
	public function get($path, $callback)
	{
		$this->routes['get'][$path] = $callback;
	}

	/**
	 * @param $path
	 * @param $callback
	 */
	public function post($path, $callback)
	{
		$this->routes['post'][$path] = $callback;
	}

	/**
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function resolve(): mixed
	{
		$path = $this->request->getPath();
		$method = $this->request->method();
		$callback = $this->routes[$method][$path] ?? false;

		if (!$callback) {
			throw new NotFoundException();
		}
		if (is_string($callback)) {
			return $this->app->view->renderView($callback);
		}

		if (is_array($callback)) {
			$callback[0] = new $callback[0]();
			$this->app->setController($callback[0]);
			$this->app->controller->action = $callback[1];
			foreach ($this->app->controller->getMiddlewares() as $middleware) {
				$middleware->execute();
			}
		}
		return call_user_func($callback, $this->request, $this->response);
	}

	/**
	 * All your base are belong to us
	 * Register the application routes here
	 * @TODO: Maybe make this more diverse/use separate files, as in a larger application, this thing will become a
	 *     monster.
	 */
	public function registerRoutes()
	{
		$this->get('/', [SiteController::class, 'home']);

		$this->get('/contact', [SiteController::class, 'contact']);
		$this->post('/contact', [SiteController::class, 'contact']);

		$this->get('/login', [AuthController::class, 'login']);
		$this->post('/login', [AuthController::class, 'login']);

		$this->get('/register', [AuthController::class, 'register']);
		$this->post('/register', [AuthController::class, 'register']);

		$this->get('/logout', [AuthController::class, 'logout']);

		$this->get('/profile', [AuthController::class, 'profile']);
	}
}