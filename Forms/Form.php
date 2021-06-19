<?php


namespace eligithub\phpmvc\Forms;


use eligithub\phpmvc\Model;
use JetBrains\PhpStorm\Pure;

class Form
{
	public static function begin(string $action, string $method): Form
	{
		echo sprintf('<form action="%s" method="%s">', $action, $method);
		return new Form();
	}

	public static function end()
	{
		echo '</form>';
	}

	public function field(Model $model, $attribute): InputField
	{
		return new InputField($model, $attribute);
	}
}