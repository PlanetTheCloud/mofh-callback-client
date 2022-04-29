# MyOwnFreeHost API Callback Client
An API callback client to parse the callback from [MyOwnFreeHost](https://myownfreehost.net/).

## Installation
This package is best installed through Composer:
```
composer require planetthecloud/mofh-callback-client
```

## Usage
The `create` method accepts the following parameters:
- ip: IP address of MyOwnFreeHost.

**Where to find the IP address of MyOwnFreeHost?**  
You can find the IP address from the [Reseller Panel](https://panel.myownfreehost.net/). Login and go to API -> Setup WHM API -> Select the domain you want to configure, and copy the IP address shown beside "MyOwnFreeHost IP address to connect to".

The following methods accepts a callable as the parameter.  
These methods will be called accordingly with the callback from MyOwnFreeHost.  
Here are the list of the methods and the parameters that will be given to the callable:
- onAccountActivated
  - username: vPanel username of the account.
  - raw: Raw callback data.
- onAccountSuspended
  - username: vPanel username of the account.
  - reason: Reason for suspension.
  - raw: Raw callback data.
- onAccountReactivated
  - username: vPanel username of the account.
  - raw: Raw callback data.
- onSqlServer
  - username: vPanel username of the account.
  - cluster: sql cluster of the account.
  - raw: Raw callback data.

The `handle` method accepts the following parameters:
- data: Callback data from MyOwnFreeHost.
- ip: (optional) IP address of the caller. **NOT MyOwnFreeHost's IP**.

## Example
```php
use PlanetTheCloud\MofhCallbackClient\Callback;

// Create a new callback handler.
$callback = Callback::create([
    'ip' => '::1' // MyOwnFreeHost IP / Allowed caller IP address
]);

// Function to be executed when an account has been successfully activated
$callback->onAccountActivated(function ($username) {
    echo "Account successfully activated: {$username}";
});

// Function to be executed when an account has been suspended
$callback->onAccountSuspended(function ($username, $reason) {
    echo "Account {$username} has been suspended with the following reason: {$reason}";
});

// Function to be executed when an account has been reactivated
$callback->onAccountReactivated(function ($username) {
    echo "Account {$username} has been reactivated";
});

// Function to be executed when SQL cluster callback is received
$callback->onSqlServer(function ($username, $cluster) {
    echo "Account {$username} has been moved to the {$cluster} cluster";
});

// Wrap in a try-catch block. See Exception section for more information
try {
    $callback->handle($_POST);
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

## Exceptions
The following exceptions are thrown by the `handle` method:
- InvalidCallbackParameters: Thrown when the callback data is invalid.
- IpAddressMismatched: Thrown when the IP address of the caller does not match the allowed IP address given in the `create` method.

## Support
Support is offered for problems related to errors/bugs that may be present in the library.  
If you seek programming support, this is not the place.  
<a href="https://discord.gg/mmEWpnwB8D"><img src="https://discordapp.com/api/guilds/399429466566426635/widget.png?style=banner2" alt="Join our Discord Server" title="Planet Dev Network"></a>

# License
Copyright 2022 PlanetTheCloud

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
