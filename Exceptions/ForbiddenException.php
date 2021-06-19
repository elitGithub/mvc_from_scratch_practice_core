<?php


namespace App\Core\Exceptions;


use App\Core\Helpers\ResponseCodes;
use Exception;

class ForbiddenException extends Exception
{
	protected $code = ResponseCodes::HTTP_FORBIDDEN;
	protected $message = 'You don\'t have permission to access this page';

}