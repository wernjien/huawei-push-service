# HUAWEI Push Service SDK
PHP SDK for the HUAWEI Push Service.

## Installation
Add the following to ```composer.json```:
```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@bitbucket.org:innoractivehackers/huawei-push-service.git"
    }
]
```
Then, run ```composer require innoractive/huawei-push-service```.

## Usage
```php
use Innoractive\HuaweiPushService\HuaweiPushService;

$accessToken = HuaweiPushService::getAccessToken($clientId, $clientSecret);
$response = HuaweiPushService::sendNotification($clientId, $accessToken, $title, $body, $deviceToken);
```
