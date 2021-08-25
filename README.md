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
After the installation completed, add ```Innoractive\HuaweiPushService\HuaweiPushServiceProvider::class``` to the autoloaded service providers array in ```config/app.php```:
```php
'providers' => [
    // ...
    Innoractive\HuaweiPushService\HuaweiPushServiceProvider::class,
],
```
Publish the config file with the command: ```php artisan vendor:publish --provider="Innoractive\HuaweiPushService\HuaweiPushServiceProvider" --tag="config"```.
A config file will be created at ```config/huawei-push-services.php```. Update the config and the setup is done!

## Usage
```php
use Innoractive\HuaweiPushService\HuaweiPushService;

// To sent notification to HUAWEI API.
HuaweiPushService::sendNotification($title,$body,$click_action,$token_device);

```
