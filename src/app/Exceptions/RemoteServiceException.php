<?php

namespace App\Exceptions;

class RemoteServiceException extends ExceptionBase
{
    protected $status = '500';

    public function __construct()
    {
        $msg = $this->build(func_get_args());

        parent::__construct($msg);
    }
}