<?php

namespace InfinityFree\MofhClient\Message;

class GetCnameResponse extends AbstractResponse
{
    protected function parseResponse()
    {
        $responseBody = (string) $this->response->getBody();
        $this->data = trim($responseBody);
    }

    /**
     * Check if the request was successful.
     */
    public function isSuccessful(): bool
    {
        return strpos($this->data, 'ERROR') !== 0;
    }

    /**
     * Get the error message, if defined.
     */
    public function getMessage(): ?string
    {
        return $this->isSuccessful() ? null : $this->getData();
    }

    /**
     * Get the returned CNAME record, if the request was successful.
     */
    public function getCname(): ?string
    {
        return $this->isSuccessful() ? $this->getData() : null;
    }
}
