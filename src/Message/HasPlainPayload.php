<?php

namespace InfinityFree\MofhClient\Message;

trait HasPlainPayload
{
    protected function parseResponse()
    {
        $responseBody = (string) $this->response->getBody();

        if (strpos($responseBody, '[') === 0) {
            $this->data = json_decode($responseBody, true);

            if ($this->data && count($this->data) == 4) {
                [$this->status, $this->domain, $this->documentRoot, $this->username] = $this->data;
            }
        } elseif ($responseBody === 'null') {
            $this->data = [];
        } else {
            $this->data = trim($responseBody);
        }
    }

    /**
     * Get the error message, if defined.
     */
    public function getMessage(): ?string
    {
        return $this->isSuccessful() ? null : $this->getData();
    }

    /**
     * Check if the request was successful.
     */
    public function isSuccessful(): bool
    {
        return is_array($this->data);
    }
}
