# HuaweiPushService SDK
HuaweiPushService SDK for PHP.

## Installation (Laravel)
Add the following to ```composer.json```:
```php
"repositories": [
    {
        "type": "vcs",
        "url": "git@bitbucket.org:innoractivehackers/huawei-push-service.git"
    }
]
```
Then, run ```composer require innoractive/huawei-push-service```.
After the installation completed, add ```Innoractive\HuaweiPushService\PushSettingServiceProvider::class``` to the autoloaded service providers array in ```config/app.php```:
```php
'providers' => [
    // ...
    Innoractive\HuaweiPushService\PushSettingServiceProvider::class,
],
```
Publish the config file with the command: ```php artisan vendor:publish --provider="Innoractive\HuaweiPushService\PushSettingServiceProvider" --tag="config"```.
A config file will be created at ```config/push-setting.php```. Update the config and the setup is done!

## Usage
```php
use Innoractive\HuaweiPushService\HuaweiPushService;

// (Not required for Laravel) Initialise HuaweiPushService API token for notification sending.
HuaweiPushService::init('HUAWEI_GRANT_TYPE', 'HUAWEI_CLIENT_ID', 'HUAWEI_CLIENT_SECRET');

// To sent notification to HUAWEI API.
HuaweiPushService::sendNotification($title,$body,$click_action,$token_device);

```
