<?php namespace Znck\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidSignatureException extends HttpException
{
    /**
     * @var array
     */
    protected $errors;

    public function __construct($errors, $statusCode, $message = null, \Exception $previous = null, array $headers = array(), $code = 0) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
        $this->errors = $errors;
    }

    public function getErrors() {
        return $this->errors;
    }

}
