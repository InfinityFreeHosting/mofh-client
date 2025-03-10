# MyOwnFreeHost API Client

An API client to use the free hosting system from [MyOwnFreeHost](https://myownfreehost.net).

**IMPORTANT: THIS LIBRARY IS AIMED AT EXPERIENCED PHP DEVELOPERS. Experience with object-oriented PHP and Composer is required. If you can't use oo-PHP and Composer, don't bother with this library.**

## Installation

This package is best installed through Composer:

```bash
composer require infinityfree/mofh-client
```

## Usage

Before you can get started, you need to get the API credentials from MyOwnFreeHost. Login to the [reseller panel](https://panel.myownfreehost.net), go to API -> Setup WHM API -> select the domain you want to configure. Copy the API Username and API password and set your own IP address as the Allowed IP Address (the IP address of your computer, server, or wherever you want to use this API client).

### Available Methods

The MyOwnFreeHost API exposes the following methods. The available parameters are listed below.

- createAccount: Create a new hosting account.
    - username: A unique, 8 character identifier of the account.
    - password: A password to login to the control panel, FTP and databases.
    - domain: A domain name to create the account. Can be a subdomain or a custom domain.
    - email: The email address of the user.
    - plan: The name of the hosting plan to create the account on. Requires a hosting package to be configured through MyOwnFreeHost.
- suspend: Suspend a hosting account.
    - username: The unique, 8 character identifier of the account.
    - reason: A string with information about why you are suspending the account.
    - linked: If true, related accounts will be suspended as well.
- unsuspend: Reactivate a hosting account.
    - username: The unique, 8 character identifier of the account.
- password: Change the password of a hosting account.
    - username: The unique, 8 character identifier of the account.
    - password: The new password to set for the account.
- availability: Check if a given domain name is available to be added to an account.
    - domain: The domain name or subdomain to check.
- getUserDomains: Get the domain names linked to a given account.
    - username: The VistaPanel login username (e.g. abcd_12345678).
- getDomainUser: Get the information of a particular hosting domain name, including the account it's hosted on and the document root.
    - domain: The domain name to search for.
- getCname: Get the CNAME subdomain for a domain name, used for CNAME domain verification.
    - username: The VistaPanel login username (e.g. abcd_12345678).
    - domain: The domain name to generate the CNAME subdomain for.

### Example

```php
use \InfinityFree\MofhClient\Client;

// Create a new API client with your API credentials.
$client = new Client("<MOFH API username>", "<MOFH API password>");

// Create a new hosting account.
$createResponse = $client->createAccount(
    'abcd1234', // A unique, 8 character identifier of the account. Primarily used as internal identifier.
    'password123', // A password to login to the control panel, FTP and databases.
    'user@example.com', // The email address of the user.
    'userdomain.example.com', // Initial domain of the account. Can be a subdomain or a custom domain.
    'my_plan', // The hosting plan name at MyOwnFreeHost.
);

// Check whether the request was successful.
if ($createResponse->isSuccessful()) {
    echo "Created account with username: ".$createResponse->getVpUsername();
} else {
   echo 'Failed to create account: ' . $createResponse->getMessage();
   die();
}
```

## License

Copyright 2025 InfinityFree

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
