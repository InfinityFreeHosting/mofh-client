<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 25-5-17
 * Time: 20:09
 */

namespace HansAdema\MofhClient\Message;


class PasswordResponse extends AbstractResponse
{
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
        if ($this->getData() && isset($this->getData()['passwd']['status'])) {
            return $this->getData()['passwd']['status'] == 1;
        } else {
            return false;
        }
    }

    protected function getMessageRules()
    {
        return array_merge(parent::getMessageRules(), [
            'the account must be active to change the password' => 'not_active',
            'error occured changing this password' => 'password_identical',
        ]);
    }
}