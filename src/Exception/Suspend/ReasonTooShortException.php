<?php

namespace HansAdema\MofhClient\Exception\Suspend;

use HansAdema\MofhClient\Exception\ApiException;

/**
 * Exception thrown when the suspension reason is less than 5 characters.
 *
 * @package HansAdema\MofhClient\Exception\Suspend
 */
class ReasonTooShortException extends ApiException
{

}