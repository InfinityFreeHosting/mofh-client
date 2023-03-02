<?php

namespace InfinityFree\MofhClient\Message;

class AvailabilityResponse extends AbstractResponse
{
    public function getMessage(): ?string
    {
        return $this->isSuccessful() ? null : $this->getData();
    }

    /**
     * Whether the request was successful.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->data, ['0', '1']);
    }

    /**
     * Whether the selected domain name is available or not.
     */
    public function isAvailable(): bool
    {
        return $this->data === '1';
    }
}
