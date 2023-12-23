<?php

namespace App\Exceptions;

class UrlNotFoundException extends ExceptionBase
{
    protected $status = '404';

    public function __construct()
    {
        $msg = $this->build(func_get_args());

        parent::__construct($msg);
    }
}