<?php

namespace HansAdema\MofhClient\Exception\Suspend;

use HansAdema\MofhClient\Exception\ApiException;

/**
 * Exception thrown if the suspension reason contains bad characters.
 *
 * Suspension reasons can only contain letters, numbers and spaces.
 *
 * @package HansAdema\MofhClient\Exception\Suspend
 */
class IllegalCharacterException extends ApiException
{

}