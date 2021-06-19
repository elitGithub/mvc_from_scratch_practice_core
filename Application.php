<?php

namespace App\Core;
use App\Core\DB\Database;
use Exception;

/**
 * Class Application
 * @package App\Core
 */
class Application
{
	public static string $ROOT_DIR;
	public static Application $app;

	public string $layout = 'main';
	public string $userClass;
	public ?Controller $controller = null;
	public Database $db;
	public Router $router;
	public Request $request;
	public Response $response;
	public Session $session;
	public ?UserModel $user;
	public View $view;

	public function __construct(string $rootPath, array $config)
	{
		static::$ROOT_DIR = $rootPath;
		$this->setApp($this);
		$this->request = new Request();
		$this->response = new Response();
		$this->router = new Router($this->request, $this->response);
		$this->session = new Session();
		$this->db = new Database($config['db']);
		$this->userClass = $config['userClass'];
		$this->view = new View();

		$primaryValue = $this->session->get('user');
		if ($primaryValue) {
			$primaryKey = $this->userClass::primaryKey();
			$this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);
		} else {
			$this->user = null;
		}
	}

	public static function isGuest(): bool
	{
		return !static::$app->user;
	}

	/**
	 * Run the application - resolve the routing.
	 */
	public function run()
	{
		try {
			echo $this->router->resolve();
		} catch (Exception $e) {
			$this->response->setStatusCode($e->getCode());
			echo $this->view->renderView('_error', ['exception' => $e]);
		}
	}

	/**
	 * @return Controller|null
	 */
	public function getController(): ?Controller
	{
		return $this->controller;
	}

	/**
	 * @param  Controller  $controller
	 */
	public function setController(Controller $controller): void
	{
		$this->controller = $controller;
	}

	/**
	 * @return Application
	 */
	public static function getApp(): Application
	{
		return static::$app;
	}

	/**
	 * @param  Application  $app
	 */
	public static function setApp(Application $app): void
	{
		static::$app = $app;
	}

	public function login(UserModel $user): bool
	{
		$this->user = $user;
		$primaryKey = $user->primaryKey();
		$primaryValue = $user->{$primaryKey};
		$this->session->set('user', $primaryValue);
		return true;
	}

	public function logout()
	{
		$this->user = null;
		$this->session->remove('user');
	}
}