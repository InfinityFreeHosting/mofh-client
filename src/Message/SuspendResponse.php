<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 24-5-17
 * Time: 13:15
 */

namespace HansAdema\MofhClient\Message;


class SuspendResponse extends AbstractResponse
{
    protected function getMessageRules()
    {
        return array_merge(parent::getMessageRules(), [
            'account is not active so can not be suspended' => 'account_not_active',
        ]);
    }
}