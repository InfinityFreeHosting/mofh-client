<?php

namespace HansAdema\MofhClient\Message;

class PasswordResponse extends AbstractResponse
{
    protected $status;

    protected function parseResponse()
    {
        parent::parseResponse();

        if (!$this->isSuccessful()) {
            if ($this->getMessage() == '') {
                $this->status = 'd';
            } else {
                $matches = [];
                if (preg_match('/the account must be active to change the password\s+\((.+)\)/', $this->getMessage(), $matches)) {
                    $this->status = $matches[1];
                }
            }
        }
    }

    /**
     * Response Message
     *
     * @return null|string A response message from the payment gateway
     */
    public function getMessage()
    {
        if ($this->getData() && isset($this->getData()['passwd']['statusmsg'])) {
            return trim($this->getData()['passwd']['statusmsg']);
        } else {
            return trim($this->response->getBody());
        }
    }

    /**
     * Whether the action was successful
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if ($this->getData() && isset($this->getData()['passwd']['status']) && $this->getData()['passwd']['status'] == 1) {
            return true; // The password call was successful
        } elseif (strpos($this->getMessage(), 'error occured changing this password') !== false) {
            return true; // The password is identical (which is technically identical to be being equal)
        } else {
            return false;
        }
    }

    public function getCode()
    {
        if (!$this->isSuccessful() && $this->getMessage() == '') {
            return 'account_deleted';
        }

        return parent::getCode();
    }

    protected function getMessageRules()
    {
        return array_merge(parent::getMessageRules(), [
            'the account must be active to change the password' => 'not_active',
            'error occured changing this password' => 'password_identical',
        ]);
    }

    /**
     * Get the status of the account which
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}