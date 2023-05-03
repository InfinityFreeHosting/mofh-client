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
        $data = (string) $this->response->getBody();

        $xmlData = @simplexml_load_string($data, \SimpleXMLElement::class, LIBXML_NOERROR);

        if ($xmlData !== false) {
            $this->data = $this->xmlToArray((array) $xmlData);
        } else {
            $this->data = $data;
        }
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

    /**
     * Recursively convert a SimpleXMLElement array to regular arrays
     */
    protected function xmlToArray(array $input): array
    {
        foreach ($input as $key => $value) {
            if ($value instanceof \SimpleXMLElement) {
                $value = (array) $value;
            }

            if (is_array($value)) {
                $input[$key] = $this->xmlToArray($value);
            }
        }

        return $input;
    }

    /**
     * Get the error message from the response if the call failed.
     */
    public function getMessage(): ?string
    {
        if ($this->isSuccessful()) {
            return null;
        } elseif ($this->getData() && isset($this->getData()['result']['statusmsg'])) {
            return trim($this->getData()['result']['statusmsg']);
        } else {
            return trim($this->response->getBody());
        }
    }

    /**
     * Whether the action was successful
     */
    public function isSuccessful(): bool
    {
        if ($this->getData() && isset($this->getData()['result']['status'])) {
            return $this->getData()['result']['status'] == 1;
        } else {
            return false;
        }
    }
}
