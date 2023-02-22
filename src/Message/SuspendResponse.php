<?php

namespace InfinityFree\MofhClient\Message;

class SuspendResponse extends AbstractResponse
{
    protected $username;

    protected $status;

    protected $reason;

    /**
     * Parse the additional parameters present in the response string.
     */
    protected function parseResponse()
    {
        parent::parseResponse();

        if (! $this->isSuccessful()) {
            if (preg_match(
                '/This account is not active so can not be suspended  \( vPuser : (\S+) ,  status : (\S+) , reason : (.+) \) ../',
                $this->getMessage(),
                $matches)
            ) {
                $this->username = $matches[1];
                $this->status = $matches[2];
                $this->reason = $matches[3];
            }
        }
    }

    /**
     * Get the status of the account if it's not active.
     *
     * The result is one of the following chars:
     * - x: suspended
     * - r: reactivating
     * - c: closing
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Get the username of the account if it's not active.
     */
    public function getVpUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Get the suspension reason of the account if it's not active.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }
}
