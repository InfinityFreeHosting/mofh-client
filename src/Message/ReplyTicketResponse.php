<?php

namespace InfinityFree\MofhClient\Message;

class ReplyTicketResponse extends AbstractResponse
{
    /**
     * Whether the reply was added successfully.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return str_contains($this->getData(), 'SUCCESS');
    }

    /**
     * The error message, if defined.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->isSuccessful() ? null : $this->getData();
    }
}
