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

**Callback Handlers**  
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
  - common_reason: Common reason for suspension, see `parseCommonSuspensionReason` section below.
- onAccountReactivated
  - username: vPanel username of the account.
  - raw: Raw callback data.
- onSqlServer
  - username: vPanel username of the account.
  - cluster: sql cluster of the account.
  - raw: Raw callback data.
- onAccountDeleted
  - username: vPanel username of the account.
  - raw: Raw callback data. 

**Handler**  
The `handle` method accepts the following parameters:
- data: Callback data from MyOwnFreeHost.
- ip: (optional) IP address of the caller. **NOT MyOwnFreeHost's IP**.

**Inspect Callback**  
There are also methods to inspect the callback before and after it is handled.  
The following methods accepts a callbable as the parameter:  
- `beforeCallback`: called before the callback is handled.  
- `afterCallback`: called after the callback is handled.  

The callable will be given the following parameters:
- data: Callback data from MyOwnFreeHost.
- ip: (optional) IP address of the caller. **NOT MyOwnFreeHost's IP**.

on the `beforeCallback` method, you can set `$this->shouldHandle = false` to prevent the callback from being handled. It is not recommended to log the callback with `beforeCallback` as it is executed before any validation is performed.

**Parsing Common Suspension Reason**
The `parseCommonSuspensionReason` method accepts the following parameters:
- reason: Raw suspension reason from MyOwnFreeHost.
Which will either return null if the reason is not related to daily suspension, or the reason itself in a short form (eg. DAILY_HIT, DAILY_CPU, DAILY_IO, DAILY_EP).  

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
$callback->onAccountSuspended(function ($username, $reason, ..., $common_reason) {
    echo "Account {$username} has been suspended with the following reason: {$reason}";
    if ($common_reason) {
        $reason = str_replace(['DAILY_EP', 'DAILY_CPU', 'DAILY_HIT', 'DAILY_IO'], ['Entry Process', 'CPU Usage', 'Website Hits', 'Input/Output'], $common_reason);
    }
    echo "Your account has been suspended because the daily {$reason} quota has been exhausted";
});

// Function to be executed when an account has been reactivated
$callback->onAccountReactivated(function ($username) {
    echo "Account {$username} has been reactivated";
});

// Function to be executed when SQL cluster callback is received
$callback->onSqlServer(function ($username, $cluster) {
    echo "Account {$username} has been moved to the {$cluster} cluster";
});

// Function to be executed when an account has been deleted
$callback->onAccountDeleted(function ($username) {
    echo "Account {$username} has been deleted";
});

// Function to be executed before the callback is handled
$callback->beforeCallback(function ($data, $ip) {
    // Do something before the callback is handled
    // Here are just an example
    if($data['status'] == 'SUSPENDED') {
        // This will skip handling of any callback with status SUSPENDED
        $this->shouldHandle = false;
    }
});

// Function to be executed after the callback is handled
$callback->afterCallback(function ($data, $ip) {
    file_put_contents('/tmp/mofh-callback-client.log', json_encode($data) . PHP_EOL, FILE_APPEND);
    echo "Callback has been logged to file";
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
