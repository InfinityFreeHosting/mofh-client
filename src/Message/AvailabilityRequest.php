<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 25-5-17
 * Time: 20:18
 */

namespace HansAdema\MofhClient\Message;


class AvailabilityRequest extends AbstractRequest
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
        $httpResponse = $this->sendRequest('checkavailable', $data);

        return $this->response = new AvailabilityResponse($this, $httpResponse);
    }

    public function getData()
    {
        $this->validate('apiUsername', 'apiPassword', 'apiUrl', 'domain');

        return [
            'api_user' => $this->getApiUsername(),
            'api_key' => $this->getApiPassword(),
            'domain' => $this->getDomain(),
        ];
    }

    protected function sendRequest($function, array $parameters)
    {
        return $this->httpClient->post($this->getApiUrl() . $function, [
            'query' => $parameters,
            'verify' => false,
        ]);
    }
}