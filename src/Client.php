<?php

namespace InfinityFree\MofhClient;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InfinityFree\MofhClient\Exception\MofhClientHttpException;
use InfinityFree\MofhClient\Message\AvailabilityResponse;
use InfinityFree\MofhClient\Message\CreateAccountResponse;
use InfinityFree\MofhClient\Message\GetCnameResponse;
use InfinityFree\MofhClient\Message\GetDomainUserResponse;
use InfinityFree\MofhClient\Message\GetUserDomainsResponse;
use InfinityFree\MofhClient\Message\PasswordResponse;
use InfinityFree\MofhClient\Message\SuspendResponse;
use InfinityFree\MofhClient\Message\UnsuspendResponse;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var ClientInterface
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
     * @var string
     */
    protected $apiUrl;

    /**
     * Create a new gateway instance
     *
     * @param  string  $apiUsername The API Username from MyOwnFreeHost.
     * @param  string  $apiPassword The API Password from MyOwnFreeHost.
     * @param  string  $apiUrl The URL of MyOwnFreeHost.net to use.
     * @param  ClientInterface|null  $httpClient An HTTP client to make API calls with.
     */
    public function __construct(string $apiUsername, string $apiPassword, string $apiUrl = 'https://panel.myownfreehost.net/xml-api/', ClientInterface $httpClient = null)
    {
        $this->apiUsername = $apiUsername;
        $this->apiPassword = $apiPassword;
        $this->apiUrl = $apiUrl;

        $this->httpClient = $httpClient ?: new Guzzle([
            'connect_timeout' => 5.0,
        ]);
    }

    /**
     * Send a POST query to the XML API.
     *
     * @param  string  $function The MOFH API function name
     * @param  array  $parameters The API function arguments
     * @return ResponseInterface
     *
     * @throws MofhClientHttpException
     */
    protected function sendPostRequest(string $function, array $parameters): ResponseInterface
    {
        return $this->sendRawRequest('POST', $function, [
            'form_params' => $parameters,
            'auth' => [$this->apiUsername, $this->apiPassword],
            'timeout' => 60.0,
        ]);
    }

    /**
     * Send a GET query to the XML API.
     *
     * @param  string  $function The MOFH API function name
     * @param  array  $parameters The API function arguments
     * @return ResponseInterface
     *
     * @throws MofhClientHttpException
     */
    protected function sendGetRequest(string $function, array $parameters): ResponseInterface
    {
        return $this->sendRawRequest('GET', $function, [
            'query' => array_replace([
                'api_user' => $this->apiUsername,
                'api_key' => $this->apiPassword,
            ], $parameters),
            'timeout' => 5.0,
        ]);
    }

    /**
     * Send the actual HTTP request to the API.
     *
     * @param string $method The HTTP method to use.
     * @param string $function The API function to use.
     * @param array $requestOptions Any Guzzle request parameters.
     * @return ResponseInterface
     * @throws MofhClientHttpException
     */
    private function sendRawRequest(string $method, string $function, array $requestOptions = []): ResponseInterface
    {
        try {
            return $this->httpClient->request($method, $this->apiUrl.$function, $requestOptions);
        } catch (GuzzleException $e) {
            throw new MofhClientHttpException('The MOFH API returned a HTTP error: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Create a new hosting account.
     *
     * @param  string  $username A custom username, max. 8 characters of letters and numbers.
     * @param  string  $password A password used to access the control panel, FTP and database.
     * @param  string  $email The contact email address of the account owner.
     * @param  string  $domain The primary domain name of the account.
     * @param  string  $plan The name of the plan to use at MyOwnFreeHost. Create this in MOFH -> Quotas & Packages -> Set Packages.
     * @return CreateAccountResponse
     *
     * @throws MofhClientHttpException
     */
    public function createAccount(string $username, string $password, string $email, string $domain, string $plan): CreateAccountResponse
    {
        $response = $this->sendPostRequest('createacct', [
            'username' => $username,
            'password' => $password,
            'contactemail' => $email,
            'domain' => $domain,
            'plan' => $plan,
        ]);

        return new CreateAccountResponse($response);
    }

    /**
     * Suspend an account on MyOwnFreeHost.
     *
     * @param  string  $username The custom username of the account. This is the 8 character username used in createAccount, not the FTP username.
     * @param  string  $reason The reason why the account is suspended. Will be prefixed with RES_CLOSE by the system.
     * @param  bool  $linked If set to true, related accounts (from the same email or IP address) will be suspended as well.
     * @return SuspendResponse
     *
     * @throws MofhClientHttpException
     */
    public function suspend(string $username, string $reason, bool $linked = false): SuspendResponse
    {
        $response = $this->sendPostRequest('suspendacct', [
            'user' => $username,
            'reason' => $reason,
            'linked' => $linked ? '1' : '0',
        ]);

        return new SuspendResponse($response);
    }

    /**
     * Unsuspend the account with the given username at MyOwnFreeHost.
     *
     * @param  string  $username The custom username of the account. This is the 8 character username used in createAccount, not the FTP username.
     * @return UnsuspendResponse
     *
     * @throws MofhClientHttpException
     */
    public function unsuspend(string $username): UnsuspendResponse
    {
        $response = $this->sendPostRequest('unsuspendacct', [
            'user' => $username,
        ]);

        return new UnsuspendResponse($response);
    }

    /**
     * Change the password of an (active) account.
     *
     * @param  string  $username The custom username of the account. This is the 8 character username used in createAccount, not the FTP username.
     * @param  string  $password The new password used to access the control panel, FTP and database.
     * @return PasswordResponse
     *
     * @throws MofhClientHttpException
     */
    public function password(string $username, string $password): PasswordResponse
    {
        $response = $this->sendPostRequest('passwd', [
            'user' => $username,
            'pass' => $password,
        ]);

        return new PasswordResponse($response);
    }

    /**
     * Check whether a domain is available to use at MyOwnFreeHost.
     *
     * This checks if the domain is in use on another account or not. It doesn't check
     *
     * @param  string  $domain The domain name or subdomain to check.
     * @return AvailabilityResponse
     *
     * @throws MofhClientHttpException
     */
    public function availability(string $domain): AvailabilityResponse
    {
        $response = $this->sendGetRequest('checkavailable', [
            'domain' => $domain,
        ]);

        return new AvailabilityResponse($response);
    }

    /**
     * Get the domains belonging to an account.
     *
     * @param  string  $username The generated username for the account (e.g. test_12345678).
     * @return GetUserDomainsResponse
     *
     * @throws MofhClientHttpException
     */
    public function getUserDomains(string $username): GetUserDomainsResponse
    {
        $response = $this->sendGetRequest('getuserdomains', [
            'username' => $username,
        ]);

        return new GetUserDomainsResponse($response);
    }

    /**
     * Get the account details corresponding to a domain name.
     *
     * @param  string  $domain The domain name to search for.
     * @return GetDomainUserResponse
     *
     * @throws MofhClientHttpException
     */
    public function getDomainUser(string $domain): GetDomainUserResponse
    {
        $response = $this->sendGetRequest('getdomainuser', [
            'domain' => $domain,
        ]);

        return new GetDomainUserResponse($response);
    }

    /**
     * Get the CNAME record corresponding to this domain, used for CNAME verification.
     *
     * @param string $username
     * @param string $domain
     * @return GetCnameResponse
     *
     * @throws MofhClientHttpException
     */
    public function getCname(string $username, string $domain): GetCnameResponse
    {
        $response = $this->sendPostRequest('getcname', [
            'api_user' => $this->apiUsername,
            'api_key' => $this->apiPassword,
            'domain_name' => $domain,
        ]);

        return new GetCnameResponse($response);
    }
}
