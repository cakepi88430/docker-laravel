<?php

namespace App\Exceptions;

class BadRequestException extends ExceptionBase
{
    protected $status = '400';

    public function __construct()
    {
        $msg = $this->build(func_get_args());

        parent::__construct($msg);
    }
}