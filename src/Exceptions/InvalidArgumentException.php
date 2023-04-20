<?php

namespace Ctexthuang\AliSdk\Exceptions;

class InvalidArgumentException extends \InvalidArgumentException
{
    public string $postErrorData  ='';

    public function __construct($message = "", $code = 0, mixed $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->postErrorData = $previous;
    }
}