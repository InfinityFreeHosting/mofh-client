<?php

namespace HansAdema\MofhClient\Exception\CreateAccount;

use HansAdema\MofhClient\Exception\ApiException;

/**
 * Exception thrown if a domain name contains too many dots
 *
 * Exception thrown if a domain name contains too many dots, which happens with a too deeply nested subdomain like sub.sub.sub.domain.com.
 *
 * @package HansAdema\MofhClient\Exception\CreateAccount
 */
class TooManyDotsException extends ApiException
{

}