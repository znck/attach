<?php namespace Znck\Attach\Exceptions;

use Exception;
use Znck\Attach\Contracts\Manipulation;

class ManipulationFailedException extends Exception
{
    private $manipulations;

    public function __construct(array $manipulations, $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->manipulations = $manipulations;
    }

    /**
     * @return Manipulation[]
     */
    public function getManipulations()
    {
        return $this->manipulations;
    }
}
