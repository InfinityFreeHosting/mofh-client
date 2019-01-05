<?php

namespace HansAdema\MofhClient;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;

class Client
{
    /**
     * @var Guzzle
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiUsername;

    /**
     * @var string
     */
    protected $apiPassword;

    /**
     * Create a new API client.
     *
     * @param string $apiUsername The API username from MOFH.
     * @param string $apiPassword The API key from MOFH.
     * @param string $url The API url (defaults to main MOFH url).
     * @param ClientInterface $httpClient A custom HTTP client to use.
     */
    public function __construct($apiUsername, $apiPassword, $url = 'https://panel.myownfreehost.net:2087/xml-api/', $httpClient = null)
    {
        $this->httpClient = $httpClient ? $httpClient : new Guzzle([
            'base_uri' => $url,
            'verify' => false,
        ]);

        $this->apiUsername = $apiUsername;
        $this->apiPassword = $apiPassword;
    }

    /**
     * Send a POST query to the WHM API
     *
     * @param string $function The WHM API function name
     * @param array $params The API function parameters
     * @return array The response data
     * @throws Exception An exception if thrown if there was a problem with the request or an error response was detected
     */
    protected function whmPost($function, array $params)
    {
        $response = $this->httpClient->post($function, [
            'form_params' => $params,
            'auth' => [$this->apiUsername, $this->apiPassword],
        ]);

        $body = trim($response->getBody());

        if (strpos($body, '<') === 0) {
            // The response starts with a <, meaning it's likely XML.
            $data = $this->xmlToArray((array)simplexml_load_string($body));

            if (isset($data['result']['status']) && $data['result']['status'] != 1) {
                throw new Exception(trim($data['result']['statusmsg']));

            } elseif (isset($data[$function]['status']) && $data[$function]['status'] != 1) {
                // This is a ridiculous exception in the WHM API spec: all calls use the "result" except for those which
                // have a completely different format, most notably the "passwd" call.
                throw new Exception(trim($data[$function]['statusmsg']));

            } else {
                return $data;
            }
        } else {
            // It's not XML, which means the API blew up.
            throw new Exception(trim($body));
        }
    }

    /**
     * Convert an array containing SimpleXMLElements to full arrays.
     *
     * @param array $input
     * @return array
     */
    protected function xmlToArray($input)
    {
        foreach ($input as $key => $value) {
            if ($value instanceof \SimpleXMLElement) {
                $value = (array)$value;
            }

            if (is_array($value)) {
                $input[$key] = $this->xmlToArray($value);
            }
        }

        return $input;
    }

    /**
     * Send a GET request to the WHM API.
     *
     * @param string $function The WHM API function name.
     * @param array $params The arguments passed to the function.
     * @return string The response body
     */
    protected function whmGet($function, array $params)
    {
        $response = $this->httpClient->get($function, [
            'query' => array_replace([
                'api_user' => $this->apiUsername,
                'api_key' => $this->apiPassword,
            ], $params),
        ]);

        return trim($response->getBody());
    }

    /**
     * Create a new hosting account.
     *
     * @param string $username A custom username, max. 8 characters of letters and numbers.
     * @param string $password The FTP, control panel and MySQL account password.
     * @param string $email The contact email address of the owner.
     * @param string $domain The domain name or subdomain of the account.
     * @param string $plan The MOFH hosting plan name.
     *
     * @return string The login username from MOFH (like host_123456789)
     * @throws Exception
     */
    public function createacct($username, $password, $email, $domain, $plan)
    {
        return $this->whmPost('createacct', [
            'username' => $username,
            'password' => $password,
            'contactemail' => $email,
            'domain' => $domain,
            'plan' => $plan,
        ])['result']['options']['vpusername'];
    }

    /**
     * Suspend an account.
     *
     * @param string $user The 8-character custom username of the account.
     * @param string $reason The reason for the suspension.
     *
     * @throws Exception
     */
    public function suspendacct($user, $reason)
    {
        $this->whmPost('suspendacct', [
            'user' => $user,
            'reason' => $reason,
        ]);
    }

    /**
     * Unsuspend an account.
     *
     * @param string $user The 8-character custom username of the account.
     *
     * @throws Exception
     */
    public function unsuspendacct($user)
    {
        $this->whmPost('unsuspendacct', [
            'user' => $user
        ]);
    }

    /**
     * Change the password of an (active) account.
     *
     * @param string $user The 8-character custom username of the account.
     * @param string $pass The new password.
     *
     * @throws Exception
     */
    public function passwd($user, $pass)
    {
        $this->whmPost('passwd', [
            'user' => $user,
            'pass' => $pass,
        ]);
    }

    /**
     * Check whether a domain is available for use on an account.
     *
     * @param string $domain The domain name or subdomain to check.
     * @return bool Whether the domain name is available or not.
     * @throws Exception
     */
    public function checkavailable($domain)
    {
        $data = $this->whmGet('checkavailable', ['domain' => $domain]);

        if ($data === '1') {
            return true;
        } elseif ($data === '0') {
            return false;
        } else {
            throw new Exception($data);
        }
    }

    /**
     * Get the domain names and their states from a specific account.
     *
     * This function returns an list of tuples of the specific domains.
     * For every domain, an array is returned with the following meaning:
     * - The first element is the status of the domain, this is typically ACTIVE or SUSPENDED.
     * - The second element is the domain name actually assigned to the account.
     *
     * @param string $username The VP username of the account to check (e.g. test_12345678).
     * @return array
     */
    public function getuserdomains($username)
    {
        $output = $this->whmGet('getuserdomains', ['username' => $username]);

        if ($output == 'null') {
            return [];
        }

        $data = json_decode($output, true);

        if ($data === null) {
            return [];
        } else {
            return $data;
        }
    }
}
