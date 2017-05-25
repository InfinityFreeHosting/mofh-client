<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 25-5-17
 * Time: 20:03
 */

namespace HansAdema\MofhClient\Message;


class UnsuspendResponse extends AbstractResponse
{
    protected function getMessageRules()
    {
        return array_merge(parent::getMessageRules(), [
            'This account is NOT currently suspended' => 'not_suspended',
            'account appears to be admin suspended' => 'admin_suspended',
        ]);
    }
}