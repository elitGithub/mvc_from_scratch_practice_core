<?php


namespace App\Core\Exceptions;


use App\Core\Helpers\ResponseCodes;
use Exception;

class NotFoundException extends Exception
{
	protected $code = ResponseCodes::HTTP_NOT_FOUND;
	protected $message = 'Page not found';

}