# MyOwnFreeHost API Client

[![CI](https://github.com/InfinityFreeHosting/mofh-client/actions/workflows/ci.yaml/badge.svg)](https://github.com/InfinityFreeHosting/mofh-client/actions/workflows/ci.yaml)
[![PHP Version](https://img.shields.io/packagist/php-v/infinityfree/mofh-client)](https://packagist.org/packages/infinityfree/mofh-client)
[![Latest Version](https://img.shields.io/packagist/v/infinityfree/mofh-client)](https://packagist.org/packages/infinityfree/mofh-client)
[![License](https://img.shields.io/packagist/l/infinityfree/mofh-client)](https://packagist.org/packages/infinityfree/mofh-client)

An API client to use the free hosting system from [MyOwnFreeHost](https://myownfreehost.net).

**IMPORTANT: THIS LIBRARY IS AIMED AT EXPERIENCED PHP DEVELOPERS. Experience with object-oriented PHP and Composer is required. If you can't use oo-PHP and Composer, don't bother with this library.**

## Requirements

- PHP 7.3 or higher
- ext-json
- ext-simplexml

## Installation

This package is best installed through Composer:

```bash
composer require infinityfree/mofh-client
```

## Usage

Before you can get started, you need to get the API credentials from MyOwnFreeHost. Login to the [reseller panel](https://panel.myownfreehost.net), go to API -> Setup WHM API -> select the domain you want to configure. Copy the API Username and API password and set your own IP address as the Allowed IP Address (the IP address of your computer, server, or wherever you want to use this API client).

```php
use InfinityFree\MofhClient\Client;

$client = new Client("<MOFH API username>", "<MOFH API password>");
```

## Available Methods

### Account Management

- **createAccount**: Create a new hosting account.
    - `username`: A unique, 8 character identifier of the account.
    - `password`: A password to login to the control panel, FTP and databases.
    - `email`: The email address of the user.
    - `domain`: A domain name to create the account. Can be a subdomain or a custom domain.
    - `plan`: The name of the hosting plan to create the account on. Requires a hosting package to be configured through MyOwnFreeHost.
- **suspend**: Suspend a hosting account.
    - `username`: The unique, 8 character identifier of the account.
    - `reason`: A string with information about why you are suspending the account.
    - `linked`: If true, related accounts will be suspended as well.
- **unsuspend**: Reactivate a hosting account.
    - `username`: The unique, 8 character identifier of the account.
- **password**: Change the password of a hosting account.
    - `username`: The unique, 8 character identifier of the account.
    - `password`: The new password to set for the account.
- **removeAccount**: Permanently delete a hosting account. The account must be suspended first.
    - `username`: The unique, 8 character identifier of the account.
- **changePackage**: Change the hosting package of an account.
    - `username`: The unique, 8 character identifier of the account.
    - `package`: The name of the new hosting package to assign.

### Domain Operations

- **availability**: Check if a given domain name is available to be added to an account.
    - `domain`: The domain name or subdomain to check.
- **getUserDomains**: Get the domain names linked to a given account.
    - `username`: The VistaPanel login username (e.g. abcd_12345678).
- **getDomainUser**: Get the information of a particular hosting domain name, including the account it's hosted on and the document root.
    - `domain`: The domain name to search for.
- **getCname**: Get the CNAME subdomain for a domain name, used for CNAME domain verification.
    - `domain`: The domain name to generate the CNAME subdomain for.

### Packages

- **listPackages**: Get a list of available hosting packages from your reseller account.

### Support Tickets

- **createTicket**: Create a new support ticket on behalf of a user.
    - `subject`: The subject of the ticket.
    - `comments`: The body of the ticket message.
    - `domain`: The domain name on behalf of which the ticket is created.
    - `username`: The username of the account to which the ticket is assigned.
    - `ipAddress`: The IP address of the user who created the ticket.
- **replyTicket**: Add a reply to an existing support ticket.
    - `ticketId`: The ID of the ticket to reply to.
    - `comments`: The body of the reply message.
    - `username`: The username of the account to which the ticket is assigned.
    - `ipAddress`: The IP address of the user who created the reply.

## Response Objects

All API methods return a response object. Every response object has these methods:

- `isSuccessful(): bool` - Whether the API call was successful.
- `getMessage(): ?string` - The error message if the call failed, or `null` on success.

Some response objects have additional methods:

| Response Class | Method | Description |
|----------------|--------|-------------|
| `CreateAccountResponse` | `getVpUsername()` | The VistaPanel username (e.g. `abcd_12345678`) |
| `CreateTicketResponse` | `getTicketId()` | The ID of the created ticket |
| `GetUserDomainsResponse` | `getDomains()` | Array of domain names on the account |
| `GetDomainUserResponse` | `getUsername()` | The account username for the domain |
| `GetDomainUserResponse` | `getDocumentRoot()` | The document root path |
| `GetDomainUserResponse` | `getStatus()` | The domain status |
| `GetCnameResponse` | `getCname()` | The CNAME subdomain for verification |
| `AvailabilityResponse` | `isAvailable()` | Whether the domain is available |
| `ListPackagesResponse` | `getPackages()` | Array of packages, each with `name`, `QUOTA`, `BWLIMIT`, etc. |

## Error Handling

There are two types of errors to handle:

### HTTP Errors

If the API is unreachable or returns an HTTP error, a `MofhClientHttpException` is thrown:

```php
use InfinityFree\MofhClient\Client;
use InfinityFree\MofhClient\Exception\MofhClientHttpException;

$client = new Client("<API username>", "<API password>");

try {
    $response = $client->createAccount('user1234', 'pass', 'user@example.com', 'example.com', 'my_plan');
} catch (MofhClientHttpException $e) {
    echo "HTTP error: " . $e->getMessage();
}
```

### API Errors

If the HTTP request succeeds but the API returns an error, check the response:

```php
$response = $client->createAccount('user1234', 'pass', 'user@example.com', 'example.com', 'my_plan');

if (!$response->isSuccessful()) {
    echo "API error: " . $response->getMessage();
}
```

## Examples

### Create an Account

```php
use InfinityFree\MofhClient\Client;

$client = new Client("<API username>", "<API password>");

$response = $client->createAccount(
    'abcd1234',
    'password123',
    'user@example.com',
    'userdomain.example.com',
    'my_plan'
);

if ($response->isSuccessful()) {
    echo "Created account with username: " . $response->getVpUsername();
} else {
    echo "Failed to create account: " . $response->getMessage();
}
```

### Suspend and Remove an Account

```php
// First suspend the account
$suspendResponse = $client->suspend('abcd1234', 'User requested account deletion');

if (!$suspendResponse->isSuccessful()) {
    die("Failed to suspend: " . $suspendResponse->getMessage());
}

// Then remove it
$removeResponse = $client->removeAccount('abcd1234');

if ($removeResponse->isSuccessful()) {
    echo "Account removed successfully";
} else {
    echo "Failed to remove account: " . $removeResponse->getMessage();
}
```

### Create a Support Ticket

```php
$response = $client->createTicket(
    'Cannot access my website',
    'I am getting a 500 error when I try to visit my website.',
    'userdomain.example.com',
    'abcd_12345678',
    '192.168.1.1'
);

if ($response->isSuccessful()) {
    echo "Ticket created with ID: " . $response->getTicketId();
} else {
    echo "Failed to create ticket: " . $response->getMessage();
}
```

### Check Domain Availability

```php
$response = $client->availability('newdomain.example.com');

if ($response->isAvailable()) {
    echo "Domain is available!";
} else {
    echo "Domain is already in use.";
}
```

### List Available Packages

```php
$response = $client->listPackages();

if ($response->isSuccessful()) {
    foreach ($response->getPackages() as $package) {
        echo $package['name'] . ": " . $package['QUOTA'] . " MB disk, " . $package['BWLIMIT'] . " MB bandwidth\n";
    }
}
```

## License

Copyright 2026 InfinityFree

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
