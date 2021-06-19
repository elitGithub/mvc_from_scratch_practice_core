<?php


namespace eligithub\phpmvc;


use eligithub\phpmvc\DB\DbModel;

abstract class UserModel extends DbModel
{
	abstract public function getDisplayName(): string;

}