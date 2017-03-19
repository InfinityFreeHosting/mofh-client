# MyOwnFreeHost API Client
An API client to use the free hosting system from [MyOwnFreeHost](http://myownfreehost.net).

## Installation

This package is best installed through Composer:
```bash
composer require hansadema/mofh-client
```

## Usage
Before you can get started, you need to get the API credentials from MyOwnFreeHost. Login to the [reseller panel](http://panel.myownfreehost.net), go to API -> Setup WHM API -> select the domain you want to configure. Copy the API Username and API password and set your own IP address as the Allowed IP Address (the IP address of your computer, server, or wherever you want to use this API client).

In your code, create the API client instance:

```php
$client = new \HansAdema\MofhClient\Client('myApiUsername', 'myApiPassword');
```

From this API client, you can use the following functions:

- `createAccount($username, $password, $email, $domain, $plan)`
- `suspend($username, $reason)`
- `unsuspend($username)`
- `password($username, $password)`
- `availability($domain)`

These are the only functions which are supported by MyOwnFreeHost at this time. For details on the parameters and responses, please see the documentation in the class itself.

A number of different exception types have been added to determine the type of error returned by MyOwnFreeHost.

## Todo
- Add more exception types (I probably missed some)
- Add unit tests

## License

Copyright 2017 Hans Adema

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.