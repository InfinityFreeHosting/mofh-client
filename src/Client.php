<?php

namespace InfinityFree\MofhClient;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InfinityFree\MofhClient\Exception\MofhClientHttpException;
use InfinityFree\MofhClient\Message\AvailabilityResponse;
use InfinityFree\MofhClient\Message\ChangePackageResponse;
use InfinityFree\MofhClient\Message\CreateAccountResponse;
use InfinityFree\MofhClient\Message\CreateTicketResponse;
use InfinityFree\MofhClient\Message\GetCnameResponse;
use InfinityFree\MofhClient\Message\GetDomainUserResponse;
use InfinityFree\MofhClient\Message\GetUserDomainsResponse;
use InfinityFree\MofhClient\Message\ListPackagesResponse;
use InfinityFree\MofhClient\Message\PasswordResponse;
use InfinityFree\MofhClient\Message\RemoveAccountResponse;
use InfinityFree\MofhClient\Message\ReplyTicketResponse;
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
     * @param  string  $apiUsername  The API Username from MyOwnFreeHost.
     * @param  string  $apiPassword  The API Password from MyOwnFreeHost.
     * @param  string  $apiUrl  The URL of MyOwnFreeHost.net to use.
     * @param  ClientInterface|null  $httpClient  An HTTP client to make API calls with.
     */
    public function __construct(
        string $apiUsername,
        string $apiPassword,
        string $apiUrl = 'https://panel.myownfreehost.net',
        ?ClientInterface $httpClient = null
    ) {
        $this->apiUsername = $apiUsername;
        $this->apiPassword = $apiPassword;

        // Clip the trailing slash from the API URL, we just want the host and port.
        if (substr($apiUrl, -1) == '/') {
            $apiUrl = substr($apiUrl, 0, -1);
        }

        // Clip the xml-api part from the API URL, we want to select which API to use ourselves.
        if (substr($apiUrl, -8) == '/xml-api') {
            $apiUrl = substr($apiUrl, 0, -8);
        }

        $this->apiUrl = $apiUrl;

        $this->httpClient = $httpClient ?: new Guzzle([
            'connect_timeout' => 5.0,
        ]);
    }

    /**
     * Send a POST query to the XML API.
     *
     * @param  string  $path  The MOFH API URL.
     * @param  array  $parameters  The API function arguments
     * @param  float  $timeout  The time to wait for a response before timing out.
     * @return ResponseInterface
     *
     * @throws MofhClientHttpException
     */
    protected function sendPostRequest(string $path, array $parameters, float $timeout = 60.0): ResponseInterface
    {
        return $this->sendRawRequest('POST', $path, [
            'form_params' => $parameters,
            'auth' => [$this->apiUsername, $this->apiPassword],
            'timeout' => $timeout,
        ]);
    }

    /**
     * Send a GET query to the XML API.
     *
     * @param  string  $path  The MOFH API URL.
     * @param  array  $parameters  The API function arguments
     * @param  float  $timeout  The time to wait for a response before timing out.
     * @return ResponseInterface
     *
     * @throws MofhClientHttpException
     */
    protected function sendGetRequest(string $path, array $parameters, float $timeout = 10.0): ResponseInterface
    {
        return $this->sendRawRequest('GET', $path, [
            'query' => array_replace([
                'api_user' => $this->apiUsername,
                'api_key' => $this->apiPassword,
            ], $parameters),
            'timeout' => $timeout,
        ]);
    }

    /**
     * Send the actual HTTP request to the API.
     *
     * @param  string  $method  The HTTP method to use.
     * @param  string  $url  The URL to the API method..
     * @param  array  $requestOptions  Any Guzzle request parameters.
     * @return ResponseInterface
     * @throws MofhClientHttpException
     */
    private function sendRawRequest(string $method, string $url, array $requestOptions = []): ResponseInterface
    {
        try {
            return $this->httpClient->request($method, "{$this->apiUrl}{$url}", $requestOptions);
        } catch (GuzzleException $e) {
            throw new MofhClientHttpException('The MOFH API returned a HTTP error: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Create a new hosting account.
     *
     * @param  string  $username  A custom username, max. 8 characters of letters and numbers.
     * @param  string  $password  A password used to access the control panel, FTP, and database.
     * @param  string  $email  The contact email address of the account owner.
     * @param  string  $domain  The primary domain name of the account.
     * @param  string  $plan  The name of the plan to use at MyOwnFreeHost. Create this in MOFH -> Quotas & Packages -> Set Packages.
     * @return CreateAccountResponse
     *
     * @throws MofhClientHttpException
     */
    public function createAccount(
        string $username,
        string $password,
        string $email,
        string $domain,
        string $plan
    ): CreateAccountResponse {
        $response = $this->sendPostRequest('/xml-api/createacct', [
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
     * @param  string  $username  The custom username of the account. This is the 8 character username used in createAccount, not the FTP username.
     * @param  string  $reason  The reason why the account is suspended. Will be prefixed with RES_CLOSE by the system.
     * @param  bool  $linked  If set to true, related accounts (from the same email or IP address) will be suspended as well.
     * @return SuspendResponse
     *
     * @throws MofhClientHttpException
     */
    public function suspend(string $username, string $reason, bool $linked = false): SuspendResponse
    {
        $response = $this->sendPostRequest('/xml-api/suspendacct', [
            'user' => $username,
            'reason' => $reason,
            'linked' => $linked ? '1' : '0',
        ]);

        return new SuspendResponse($response);
    }

    /**
     * Unsuspend the account with the given username at MyOwnFreeHost.
     *
     * @param  string  $username  The custom username of the account. This is the 8 character username used in createAccount, not the FTP username.
     * @return UnsuspendResponse
     *
     * @throws MofhClientHttpException
     */
    public function unsuspend(string $username): UnsuspendResponse
    {
        $response = $this->sendPostRequest('/xml-api/unsuspendacct', [
            'user' => $username,
        ]);

        return new UnsuspendResponse($response);
    }

    /**
     * Change the password of an (active) account.
     *
     * @param  string  $username  The custom username of the account. This is the 8-character username used in createAccount, not the FTP username.
     * @param  string  $password  The new password used to access the control panel, FTP, and database.
     * @return PasswordResponse
     *
     * @throws MofhClientHttpException
     */
    public function password(string $username, string $password): PasswordResponse
    {
        $response = $this->sendPostRequest('/xml-api/passwd', [
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
     * @param  string  $domain  The domain name or subdomain to check.
     * @return AvailabilityResponse
     *
     * @throws MofhClientHttpException
     */
    public function availability(string $domain): AvailabilityResponse
    {
        $response = $this->sendGetRequest('/xml-api/checkavailable', [
            'domain' => $domain,
        ]);

        return new AvailabilityResponse($response);
    }

    /**
     * Get the domains belonging to an account.
     *
     * @param  string  $username  The generated username for the account (e.g. test_12345678).
     * @return GetUserDomainsResponse
     *
     * @throws MofhClientHttpException
     */
    public function getUserDomains(string $username): GetUserDomainsResponse
    {
        $response = $this->sendGetRequest('/json-api/getuserdomains', [
            'username' => $username,
        ]);

        return new GetUserDomainsResponse($response);
    }

    /**
     * Get the account details corresponding to a domain name.
     *
     * @param  string  $domain  The domain name to search for.
     * @return GetDomainUserResponse
     *
     * @throws MofhClientHttpException
     */
    public function getDomainUser(string $domain): GetDomainUserResponse
    {
        $response = $this->sendGetRequest('/json-api/getdomainuser', [
            'domain' => $domain,
        ]);

        return new GetDomainUserResponse($response);
    }

    /**
     * Get the CNAME record corresponding to this domain, used for CNAME verification.
     *
     * @param  string  $domain  The domain name to search for.
     * @return GetCnameResponse
     *
     * @throws MofhClientHttpException
     */
    public function getCname(string $domain): GetCnameResponse
    {
        $response = $this->sendPostRequest('/xml-api/getcname', [
            'api_user' => $this->apiUsername,
            'api_key' => $this->apiPassword,
            'domain_name' => $domain,
        ]);

        return new GetCnameResponse($response);
    }

    /**
     * Get the available hosting packages from the reseller account.
     *
     * @return ListPackagesResponse
     * @throws MofhClientHttpException
     */
    public function listPackages(): ListPackagesResponse
    {
        $response = $this->sendGetRequest('/json-api/listpkgs', []);

        return new ListPackagesResponse($response);
    }

    /**
     * Remove the hosting account of the given username.
     *
     * Note: the account must be suspended before calling this method.
     *
     * @param  string  $username  The custom username of the account. This is the 8-character username used in createAccount, not the FTP username.
     * @return RemoveAccountResponse
     * @throws MofhClientHttpException
     */
    public function removeAccount(string $username): RemoveAccountResponse
    {
        $response = $this->sendPostRequest('/xml-api/removeacct', [
            'user' => $username,
        ]);

        return new RemoveAccountResponse($response);
    }

    /**
     * Change the hosting package of the given username.
     *
     * @param  string  $username  The custom username of the account. This is the 8-character username used in createAccount, not the FTP username.
     * @param  string  $package
     * @return ChangePackageResponse
     * @throws MofhClientHttpException
     */
    public function changePackage(string $username, string $package): ChangePackageResponse
    {
        $response = $this->sendPostRequest('/xml-api/changepackage', [
            'user' => $username,
            'pkg' => strtolower($package),
        ]);

        return new ChangePackageResponse($response);
    }

    /**
     * Create a new support ticket on behalf of a user account.
     *
     * @param  string  $subject  The subject of the ticket.
     * @param  string  $comments  The body of the ticket message.
     * @param  string  $domain  The domain name on behalf of which the ticket is created.
     * @param  string  $username  The username of the account to which the ticket is assigned.
     * @param  string  $ipAddress  The IP address of the user who created the ticket.
     *
     * @return CreateTicketResponse
     * @throws MofhClientHttpException
     */
    public function createTicket(
        string $subject,
        string $comments,
        string $domain,
        string $username,
        string $ipAddress
    ): CreateTicketResponse {
        $response = $this->sendPostRequest('/xml-api/supportnewticket', [
            'api_user' => $this->apiUsername,
            'api_key' => $this->apiPassword,
            'comments' => $comments,
            'subject' => $subject,
            'domain_name' => $domain,
            'ipaddress' => $ipAddress,
            'clientusername' => $username,
        ]);

        return new CreateTicketResponse($response);
    }

    /**
     * Add a reply to an existing support ticket.
     *
     * @param  string  $ticketId  The ticket ID to reply to.
     * @param  string  $comments  The body of the ticket message.
     * @param  string  $username  The username of the account to which the ticket is assigned.
     * @param  string  $ipAddress  The IP address of the user who created the ticket.
     * @param  string|null  $domain (Optional) The domain name to which this ticket is created.
     * @return ReplyTicketResponse
     * @throws MofhClientHttpException
     */
    public function replyTicket(
        string $ticketId,
        string $comments,
        string $username,
        string $ipAddress,
        ?string $domain = null
    ): ReplyTicketResponse {
        $response = $this->sendPostRequest('/xml-api/supportreplyticket', [
            'api_user' => $this->apiUsername,
            'api_key' => $this->apiPassword,
            'comments' => $comments,
            'ipaddress' => $ipAddress,
            'clientusername' => $username,
            'ticket_id' => $ticketId,
            'domain_name' => $domain,
        ]);

        return new ReplyTicketResponse($response);
    }
}
