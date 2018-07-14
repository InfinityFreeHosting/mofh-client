# MyOwnFreeHost API Client
An API client to use the free hosting system from [MyOwnFreeHost](http://myownfreehost.net).

## Installation

This package is best installed through Composer:
```bash
composer require hansadema/mofh-client
```

## Usage
Before you can get started, you need to get the API credentials from MyOwnFreeHost. Login to the [reseller panel](http://panel.myownfreehost.net), go to API -> Setup WHM API -> select the domain you want to configure. Copy the API Username and API password and set your own IP address as the Allowed IP Address (the IP address of your computer, server, or wherever you want to use this API client).

The MyOwnFreeHost API exposes the following methods. The available parameters are listed below.
- createAccount
    - username: A unique, 8 character identifier of the account.
    - password: A password to login to the control panel, FTP and databases.
    - domain: A domain name to create the account. Can be a subdomain or a custom domain.
    - email: The email address of the user.
    - plan: The name of the hosting plan to create the account on. Requires a hosting package to be configured through MyOwnFreeHost.
- suspend
    - username: The unique, 8 character identifier of the account.
    - reason: A string with information about why you are suspending the account.
- unsuspend
    - username: The unique, 8 character identifier of the account.
- password
    - username: The unique, 8 character identifier of the account.
    - password: The new password to set for the account.
- availability
    - domain: The domain name or subdomain to check.

### Example

```php
use \HansAdema\MofhClient\Client;

### Check the availability or a domain name

You can use the `checkavailable` function to check whether a domain name or subdomain can be added to an account.

```php
$client = new \HansAdema\MofhClient\Client('myApiUsername', 'myApiPassword');

try {
    $result = $client->checkavailable('example.com');
    
    $error = 'The domain name is already in use.';
} catch (\HansAdema\MofhClient\Exception $e) {
    $result = false;
    
    $error = $e->getMessage();
}

if ($result) {
    echo "The domain name is available!";
} else {
    echo "The domain name cannot be registered: ".$error;
}
```

### Create a hosting account

```php
$client = new \HansAdema\MofhClient\Client('myApiUsername', 'myApiPassword');

// A unique, 8 character username to identify the account.
$username = 'test1234'; 

// The password for the hosting account's control panel, FTP account and MySQL Databases.
$password = 'password';

// The email address of the user creating the account.
$email = 'example@gmail.com';

// The first domain name to add to the account (subdomain or custom domain).
$domain = 'example.com';

// The hosting plan of the account. Go to https://panel.myownfreehost.net -> Quotas & Packages -> Set Packages to create a hosting plan. 
$plan = 'myplan';

try {
    $vpUsername = $client->createacct($username, $password, $email, $domain, $plan);
    
    echo "Your account is now being created! Your control panel and FTP username is: ".$vpUsername;
} catch (\HansAdema\MofhClient\Exception $e) {
    echo "Your account could not be created: ".$e->getMessage();
}
```

### Change the password of a hosting account

The `passwd` call can be used to update the password of a hosting account. This will update the control panel password, FTP password and MySQL password.

```php
$client = new \HansAdema\MofhClient\Client('myApiUsername', 'myApiPassword');

// The unique, 8 character username to identify the account.
$username = 'test1234';

$newPassword = 'password123';

try {
    $client->passwd($username, $newPassword);
    
    echo 'Your password was updated successfully.';
} catch (\HansAdema\MofhClient\Exception $e) {
     echo 'Your passwor could not be saved: '.$e->getMessage();
}
```

### Suspend a hosting account

You can use the `suspendacct` call to suspend a hosting account. A suspended account cannot serve websites and cannot access files or databases.

```php
$client = new \HansAdema\MofhClient\Client('myApiUsername', 'myApiPassword');

// The unique, 8 character username to identify the account.
$username = 'test1234';

$reason = 'This person is a baddie!';

try {
    $client->suspendacct($username, $reason);
    
    echo 'The account is now being suspended!';
} catch (\HansAdema\MofhClient\Exception $e) {
    echo 'The account could not be suspended: '.$e->getMessage();
}
```

### Unsuspend a hosting account

To revert an account suspension, you can use the `unsuspendacct` call. Note that you can only unsuspend accounts you suspended yourself. Accounts which were suspended by iFastNet can only be reactivated by iFastNet.

```php
$client = new \HansAdema\MofhClient\Client('myApiUsername', 'myApiPassword');

// The unique, 8 character username to identify the account.
$username = 'test1234';

try {
    $client->unsuspendacct($username);
    
    echo 'The account is now being reactivated!';
} catch (\HansAdema\MofhClient\Exception $e) {
    echo 'The account could not be reactivated: '.$e->getMessage();
}
```

## License

Copyright 2019 Hans Adema

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
