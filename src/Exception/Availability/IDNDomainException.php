<?php

namespace HansAdema\MofhClient\Exception\Availability;

use HansAdema\MofhClient\Exception\ApiException;

/**
 * Exception thrown if an IDN domain is submitted (IDN domains are not supported)
 *
 * @package HansAdema\MofhClient\Exception\Availability
 */
class IDNDomainException extends ApiException
{

}