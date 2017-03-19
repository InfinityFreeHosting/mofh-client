<?php

namespace HansAdema\MofhClient\Exception\Availability;

use HansAdema\MofhClient\Exception\ApiException;

/**
 * An exception thrown when the domain name contains a blacklisted keyword
 *
 * @package HansAdema\MofhClient\Exception\Availability
 */
class BlacklistedKeywordException extends ApiException
{

}