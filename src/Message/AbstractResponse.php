<?php

namespace InfinityFree\MofhClient\Message;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractResponse
{
    /**
     * The data contained in the response.
     *
     * @var mixed
     */
    protected $data;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Create a new Response object.
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        $this->parseResponse();
    }

    /**
     * Parse the response after it has been received.
     */
    protected function parseResponse()
    {
        $this->data = trim((string) $this->response->getBody());
    }

    /**
     * Get the response data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    abstract public function isSuccessful(): bool;

    abstract public function getMessage(): ?string;
}
