<?php


namespace eligithub\phpmvc\Exceptions;


use eligithub\phpmvc\Helpers\ResponseCodes;
use Exception;

class NotFoundException extends Exception
{
	protected $code = ResponseCodes::HTTP_NOT_FOUND;
	protected $message = 'Page not found';

}