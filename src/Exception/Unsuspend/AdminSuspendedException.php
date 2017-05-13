<?php

namespace HansAdema\MofhClient\Exception\Unsuspend;

use HansAdema\MofhClient\Exception\ApiException;

/**
 * Exception thrown if the account cannot be reactivated by the reseller.
 *
 * You should contact iFastNet to get the account reactivated.
 *
 * @package HansAdema\MofhClient\Exception\Password
 */
class AdminSuspendedException extends ApiException
{

}