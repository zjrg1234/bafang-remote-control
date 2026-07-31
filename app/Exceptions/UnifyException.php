<?php

namespace App\Exceptions;

use Exception;

class UnifyException extends Exception
{
    public function render($request)
    {
        return [
            'code' => $this->code,
            'msg'  => $this->message,
        ];
    }
}