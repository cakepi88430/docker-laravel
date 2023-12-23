<?php

namespace App\Exceptions;

class NotFoundException extends ExceptionBase
{
    protected $status = '404';

    protected $id = 'not_found';

    protected $title = 'Resource Not Found';

    public function __construct()
    {
        $msg = $this->build(func_get_args());

        parent::__construct($msg);
    }
}