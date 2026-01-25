<?php

namespace InfinityFree\MofhClient\Message;

trait HasJsonPayload
{
    /**
     * Parse the response after it has been received.
     */
    protected function parseResponse(): void
    {
        $data = (string) $this->response->getBody();

        $jsonData = json_decode($data, true);

        if ($jsonData !== false) {
            $this->data = $jsonData;
        } else {
            $this->data = trim($data);
        }
    }

    /**
     * Get the error message from the response if the call failed.
     */
    public function getMessage(): ?string
    {
        if ($this->isSuccessful()) {
            return null;
        } elseif ($this->getData() && isset($this->getData()['cpanelresult']['error'])) {
            return trim($this->getData()['cpanelresult']['error']);
        } else {
            return trim($this->response->getBody());
        }
    }

    /**
     * Whether the action was successful
     */
    public function isSuccessful(): bool
    {
        if ($this->getData()) {
            return !isset($this->getData()['cpanelresult']['error']);
        } else {
            return false;
        }
    }
}
