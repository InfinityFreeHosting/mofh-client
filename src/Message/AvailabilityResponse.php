<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 25-5-17
 * Time: 20:20
 */

namespace HansAdema\MofhClient\Message;


class AvailabilityResponse extends AbstractResponse
{
    /**
     * Constructor
     *
     * @param mixed $request the initiating request.
     * @param mixed $response
     */
    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->data = (string)$response->getBody();
    }

    public function getMessage()
    {
        return $this->getData();
    }

    public function isSuccessful()
    {
        return $this->data === '1' || $this->data === '0';
    }

    public function isAvailable()
    {
        return $this->data === '1';
    }
}