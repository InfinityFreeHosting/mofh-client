<?php

namespace InfinityFree\MofhClient\Message;

class CreateTicketResponse extends AbstractResponse
{
    /**
     * Whether the ticket was created successfully.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return str_contains($this->data, 'SUCCESS');
    }

    /**
     * Get the error message, if defined.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->isSuccessful() ? null : $this->getData();
    }

    /**
     * Get the ticket ID, if the ticket was created successfully.
     *
     * @return string|null
     */
    public function getTicketId(): ?string
    {
        if ($this->isSuccessful()) {
            return trim(explode(':', substr($this->data, strpos($this->data, 'SUCCESS')))[1]);
        } else {
            return null;
        }
    }
}
