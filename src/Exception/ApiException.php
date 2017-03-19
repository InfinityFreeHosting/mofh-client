<?php

namespace HansAdema\MofhClient\Exception;

class ApiException extends \Exception
{
    /**
     * @var mixed
     */
    protected $response;

    /**
     * ApiException constructor.
     *
     * @param string $message
     * @param mixed $response
     */
    public function __construct($message, $response)
    {
        parent::__construct($message);

        $this->response = $response;
    }

    /**
     * Get the response returned by the API
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}