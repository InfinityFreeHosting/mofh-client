<?php

namespace HansAdema\MofhClient\Exception\CreateAccount;

use HansAdema\MofhClient\Exception\ApiException;

/**
 * Exception thrown if the domain is not pointing to the nameservers of MOFH
 *
 * @package HansAdema\MofhClient\Exception\CreateAccount
 */
class InvalidNameserversException extends ApiException
{

}