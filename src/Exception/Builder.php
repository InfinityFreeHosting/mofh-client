<?php

namespace HansAdema\MofhClient\Exception;

use HansAdema\MofhClient\Exception\Availability\BlacklistedKeywordException;
use HansAdema\MofhClient\Exception\Availability\DomainTooLongException;
use HansAdema\MofhClient\Exception\Availability\DomainTooShortException;
use HansAdema\MofhClient\Exception\Availability\HttpPrefixException;
use HansAdema\MofhClient\Exception\Availability\IDNDomainException;
use HansAdema\MofhClient\Exception\Availability\IllegalCharacterException;
use HansAdema\MofhClient\Exception\Availability\TKDomainException;
use HansAdema\MofhClient\Exception\CreateAccount\DomainExistsException;
use HansAdema\MofhClient\Exception\CreateAccount\InvalidNameserversException;
use HansAdema\MofhClient\Exception\CreateAccount\UsernameExistsException;
use HansAdema\MofhClient\Exception\Password\PasswordIdenticalException;
use HansAdema\MofhClient\Exception\Response\InvalidApiKeyException;
use HansAdema\MofhClient\Exception\Response\InvalidApiUsernameException;
use HansAdema\MofhClient\Exception\Response\InvalidIpAddressException;
use HansAdema\MofhClient\Exception\Response\UnknownUsernameException;
use HansAdema\MofhClient\Exception\Suspend\AccountNotActiveException;
use HansAdema\MofhClient\Exception\Unsuspend\AccountNotSuspendedException;

class Builder
{

    /**
     * Define the message to exception mapping
     *
     * @return array
     */
    private static function rules()
    {
        return [
            'The API key you are using appears to be invalid' => InvalidApiKeyException::class,
            'The API username you are using appears to be invalid' => InvalidApiUsernameException::class,
            'does not match the allowed ip address' => InvalidIpAddressException::class,
            'No account mathcing this username' => UnknownUsernameException::class,
            'this domdin name appears invalid (to long !)' => DomainTooLongException::class,
            'domain name choosen does not appear to be valid / allowed' => BlacklistedKeywordException::class,
            'we do not support IDN domains' => IDNDomainException::class,
            '/The username \w+ appears to be allready created/' => UsernameExistsException::class,
            '/The domain name [\w|\.]+ is allready added to a hosting account/i' => DomainExistsException::class,
            '/The name servers for [\w\.]+ are not set to valid name servers/i' => InvalidNameserversException::class,
            'account is not active so can not be suspended' => AccountNotActiveException::class,
            'This account is NOT currently suspended' => AccountNotSuspendedException::class,
            'the account must be active to change the password' => \HansAdema\MofhClient\Exception\Password\AccountNotActiveException::class,
            'error occured changing this password' => PasswordIdenticalException::class,
            'domain name appears invalid (to short !)' => DomainTooShortException::class,
            'Sorry we do not support hosting .tk domains on free hosting' => TKDomainException::class,
            'Illegal charachters in domain name .epizzy.com' => IllegalCharacterException::class,
            'The domain name choosen is not allowd' => BlacklistedKeywordException::class,
            'http:// should NOT be added to the domain name' => HttpPrefixException::class,
        ];
    }

    /**
     * Get the appropriate exception type of each message
     *
     * @param string $message
     * @param mixed $data
     * @return ApiException
     */
    public static function build($message, $data)
    {
        foreach (static::rules() as $rule => $class) {
            if (substr($rule, 0, 1) == '/') {
                if (preg_match($rule, $message)) {
                    return new $class($message, $data);
                }
            } else {
                if (strpos($message, $rule) !== false) {
                    return new $class($message, $data);
                }
            }
        }

        return new ApiException($message, $data);
    }
}