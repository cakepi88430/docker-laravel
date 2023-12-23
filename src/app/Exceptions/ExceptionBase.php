<?php
namespace App\Exceptions;

use Exception;

abstract class ExceptionBase extends Exception
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $detail = '%s';

    /**
     * @param string $msg message to the parent class's message
     * @return void
     */
    public function __construct($msg)
    {
        parent::__construct($msg);
    }

    /**
     * Get the status code
     * @return int status code in int
     */
    public function getStatus()
    {
        return (int) $this->status;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'title' => $this->title,
            'detail' => $this->detail
        ];
    }

    protected function build(array $args)
    {
        if (!empty($args)) {
            $this->id = array_shift($args);
        }

        $error = config(sprintf('errors.%s', $this->id));
        if (isset($error['title'])) {
            $this->title = $error['title'];
        }

        if (empty($args)) {
            $this->detail = vsprintf($error['detail'], ['']);
        } else {
            $this->detail = vsprintf($error['detail'], $args);
        }

        return $this->detail;
    }
}