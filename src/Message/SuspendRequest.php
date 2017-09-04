<?php

namespace HansAdema\MofhClient\Message;

class SuspendRequest extends AbstractRequest
{
    public function getReason()
    {
        return $this->getParameter('reason');
    }

    public function setReason($reason)
    {
        return $this->setParameter('reason', $reason);
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('suspendacct', $data);

        return $this->response = new SuspendResponse($this, $httpResponse);
    }

    public function getData()
    {
        $this->validate('apiUsername', 'apiPassword', 'apiUrl', 'username', 'reason');

        return [
            'user' => $this->getUsername(),
            'reason' => $this->getReason(),
        ];
    }
}