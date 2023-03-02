<?php

namespace InfinityFree\MofhClient\Message;

class PasswordResponse extends AbstractResponse
{
    protected $status;

    protected $message;

    protected function parseResponse()
    {
        parent::parseResponse();

        if (isset($this->getData()['passwd']['status'])) {
            if ($this->getData()['passwd']['status'] == '1') {
                $this->status = 'a';
            } elseif (strpos($this->getData()['passwd']['statusmsg'], 'error occured changing this password') !== false) {
                // This error means the password is identical. We consider this to be successful (making this call idempotent).
                $this->status = 'a';
            } else {
                $this->message = trim($this->getData()['passwd']['statusmsg']);
                if (preg_match('/the account must be active to change the password\s+\((.+)\)/', $this->message, $matches)) {
                    $this->status = $matches[1];
                }
            }
        } else {
            $this->message = trim($this->response->getBody());
        }
    }

    /**
     * Get the error message of the response, if it failed.
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Whether the action was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status == 'a';
    }

    /**
     * Get the status of the account if the account is not active.
     *
     * The result is one of the following chars:
     * - a: active
     * - x: suspended
     * - r: reactivating
     * - c: closing
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }
}
