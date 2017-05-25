<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 25-5-17
 * Time: 20:09
 */

namespace HansAdema\MofhClient\Message;


class PasswordRequest extends AbstractRequest
{
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($password)
    {
        return $this->setParameter('password', $password);
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('passwd', $data);

        return $this->response = new PasswordResponse($this, $httpResponse);
    }

    public function getData()
    {
        $this->validate('apiUsername', 'apiPassword', 'apiUrl', 'username', 'password');

        return [
            'user' => $this->getUsername(),
            'pass' => $this->getPassword(),
        ];
    }
}