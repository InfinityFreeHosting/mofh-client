<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 25-5-17
 * Time: 20:03
 */

namespace HansAdema\MofhClient\Message;


class UnsuspendRequest extends AbstractRequest
{
    public function getDomain()
    {
        return $this->getParameter('domain');
    }

    public function setDomain($domain)
    {
        return $this->setParameter('domain', $domain);
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->get($this->getApiUrl() . 'checkavailable', [
            'query' => $data,
            'verify' => false,
        ]);

        return $this->response = new UnsuspendResponse($this, $httpResponse);
    }

    public function getData()
    {
        $this->validate('apiUsername', 'apiPassword', 'apiUrl', 'username');

        return [
            'user' => $this->getUsername(),
        ];
    }
}