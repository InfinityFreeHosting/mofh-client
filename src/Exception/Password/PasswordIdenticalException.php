<?php

namespace HansAdema\MofhClient\Exception\Password;

use HansAdema\MofhClient\Exception\ApiException;

/**
 * Exception thrown if the submitted password is identical to the current password
 *
 * @package HansAdema\MofhClient\Exception\Password
 */
class PasswordIdenticalException extends ApiException
{

}