<?php

namespace HansAdema\MofhClient\Message;

class UnsuspendResponse extends AbstractResponse
{
    protected $status;

    protected function parseResponse()
    {
        parent::parseResponse();

        $matches = [];

        if (!$this->isSuccessful()) {
            if (preg_match('/account is NOT currently suspended \(status : (\w*) \)/', $this->getMessage(), $matches)) {
                if (trim($matches[1]) == '') {
                    $this->status = 'd';
                } else {
                    $this->status = trim($matches[1]);
                }
            }
        }
    }

    public function getStatus()
    {
        return $this->status;
    }

    protected function getMessageRules()
    {
        return array_merge(parent::getMessageRules(), [
            'This account is NOT currently suspended' => 'not_suspended',
            'account appears to be admin suspended' => 'admin_suspended',
        ]);
    }
}