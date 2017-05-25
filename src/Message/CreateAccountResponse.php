<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 25-5-17
 * Time: 19:54
 */

namespace HansAdema\MofhClient\Message;


class CreateAccountResponse extends AbstractResponse
{
    /**
     * Get the VistaPanel username of the account (like test_123456789)
     *
     * @return null|string
     */
    public function getVpUsername()
    {
        if (isset($this->getData()['result']['options']['vpusername'])) {
            return $this->getData()['result']['options']['vpusername'];
        } else {
            return null;
        }
    }

    protected function getMessageRules()
    {
        return array_merge(parent::getMessageRules(), [
            '/The username \w+ appears to be allready created/' => 'username_exists',
            '/The domain name [\w\.-]+ is allready added to a hosting account/i' => 'domain_exists',
            '/The name servers for [\w\.-]+ are not set to valid name servers/i' => 'invalid_nameservers',
            'The domain appears to belong to another reseller' => 'domain_other_reseller',
            'The domain name does not appear valid (to many dots !)' => 'domain_too_many_dots',
        ]);
    }
}