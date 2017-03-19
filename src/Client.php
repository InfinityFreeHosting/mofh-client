<?php

namespace HansAdema\MofhClient;

use GuzzleHttp\Client as Guzzle;
use HansAdema\MofhClient\Exception\ApiException;
use HansAdema\MofhClient\Exception\Builder;

class Client
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiUsername;

    /**
     * @var string
     */
    protected $apiPassword;

    /**
     * Create a new API client
     *
     * @param string $apiUsername The API username from MOFH
     * @param string $apiPassword The API key from MOFH
     * @param string $url The API url (defaults to main MOFH url)
     */
    public function __construct($apiUsername, $apiPassword, $url = 'https://panel.myownfreehost.net:2087/xml-api/')
    {
        $this->client = new Guzzle([
            'base_uri' => $url,
        ]);

        $this->apiUsername = $apiUsername;
        $this->apiPassword = $apiPassword;
    }

    /**
     * Send a POST query to the XML API
     *
     * @param string $function The MOFH API function name
     * @param array $options The API function arguments
     * @return array The response data
     * @throws ApiException An exception if thrown if there was a problem with the request or an error response was detected
     */
    protected function query($function, array $options)
    {
        $response = $this->client->post($function, [
            'form_params' => $options,
            'auth' => [$this->apiUsername, $this->apiPassword],
            'verify' => false,
        ]);

        $data = (string)$response->getBody();

        if (strpos(trim($data), '<') !== 0) {
            throw Builder::build($data, $data);
        }

        $array = $this->xmlToArray((array)simplexml_load_string($data));

        if (isset($array['result']['status']) && $array['result']['status'] != 1) {
            throw Builder::build($array['result']['statusmsg'], $array);
        } else {
            return $array;
        }
    }

    /**
     * Convert an array containing SimpleXMLElements to full arrays
     *
     * @param array $input
     * @return array
     */
    private function xmlToArray($input)
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
     * Create a new hosting account
     *
     * @param string $username A custom username, max. 8 characters of letters and numbers
     * @param string $password The account password
     * @param string $email The email address of the owner
     * @param string $domain The domain name of the account
     * @param string $plan The MOFH hosting plan name
     * @return string The login username from MOFH (like host_123456789)
     * @throws ApiException
     */
    public function createAccount($username, $password, $email, $domain, $plan)
    {
        return $this->query('createacct', [
            'username' => $username,
            'password' => $password,
            'contactemail' => $email,
            'domain' => $domain,
            'plan' => $plan,
        ])['result']['options']['vpusername'];
    }

    /**
     * Suspend an account on MOFH
     *
     * @param string $username The custom username of the account
     * @param string $reason The reason for the suspension
     * @throws ApiException
     */
    public function suspend($username, $reason)
    {
        $this->query('suspendacct', [
            'user' => $username,
            'reason' => $reason,
        ]);
    }

    /**
     * Unsuspend the account with the given username at MOFH
     *
     * @param string $username The custom username of the account
     * @throws ApiException
     */
    public function unsuspend($username)
    {
        $this->query('unsuspendacct', ['user' => $username]);
    }

    /**
     * Change the password of an (active) account
     *
     * @param string $username The custom username of the account
     * @param string $password The new password
     * @throws ApiException
     */
    public function password($username, $password)
    {
        $response = $this->query('passwd', [
            'user' => $username,
            'pass' => $password,
        ]);

        if (isset($response['passwd']['status']) && $response['passwd']['status'] != 1) {
            throw Builder::build($response['passwd']['statusmsg'], $response);
        }
    }

    /**
     * Check whether a domain is available at MOFH
     *
     * @param string $domain The domain to check
     * @return bool
     * @throws ApiException
     */
    public function availability($domain)
    {
        $response = $this->client->get('checkavailable', [
            'query' => [
                'api_user' => $this->apiUsername,
                'api_key' => $this->apiPassword,
                'domain' => $domain,
            ],
            'verify' => false,
        ]);

        $data = trim($response->getBody());

        if ($data === '1') {
            return true;
        } elseif ($data === '0') {
            return false;
        } else {
            throw Builder::build($data, $data);
        }
    }
}