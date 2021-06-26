<?php


namespace eligithub\phpmvc\Helpers;


class DefaultAppRoutes
{
	public static function defaultAppRoutes()
	{
		return [
			'get' => [
				[
					'path'     => '/',
					'callback' => [SiteController::class, 'home']
				],
				[
					'path'     => '/contact',
					'callback' => [SiteController::class, 'contact']
				],
				[
					'path'     => '/login',
					'callback' => [AuthController::class, 'login']
				],
				[
					'path'     => '/register',
					'callback' => [AuthController::class, 'register']
				],
				[
					'path'     => '/logout',
					'callback' => [AuthController::class, 'logout']
				],
				[
					'path'     => '/profile',
					'callback' => [AuthController::class, 'profile']
				],
			],
			'post' => [
				[
					'path'     => '/contact',
					'callback' => [SiteController::class, 'contact']
				],
				[
					'path'     => '/login',
					'callback' => [AuthController::class, 'login']
				],
				[
					'path'     => '/register',
					'callback' => [AuthController::class, 'register']
				],
			],
		];
	}

}