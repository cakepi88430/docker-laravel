<?php

namespace App\Exceptions;

class NotImplementedException extends ExceptionBase
{
    protected $status = '501';

    public function __construct()
    {
        $msg = $this->build(func_get_args());

        parent::__construct($msg);
    }
}