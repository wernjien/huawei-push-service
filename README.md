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

`$deviceToken` accepts either a single token string or an array of tokens.

### Click action
By default, tapping the notification opens the app with no custom action (`type` `3`, i.e. `IGNORE`). Pass a custom `$action` array as the 6th argument to override this, e.g. `['type' => 2, 'url' => 'https://example.com']` to open a URL, or `['type' => 1, 'intent' => '...']` to launch a custom intent. See HUAWEI's Push Kit `click_action` documentation for all supported types.

### Error handling
Both methods let HTTP-level failures (bad credentials, network issues, timeouts) bubble up as `GuzzleHttp\Exception\GuzzleException`. `sendNotification()` also throws `InvalidArgumentException` if `$deviceToken` resolves to an empty array. Wrap calls in a `try`/`catch` to handle these:

```php
try {
    $accessToken = HuaweiPushService::getAccessToken($clientId, $clientSecret);
    $response = HuaweiPushService::sendNotification($clientId, $accessToken, $title, $body, $deviceToken);
} catch (\InvalidArgumentException $e) {
    // no device token was supplied
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    // request failed (bad credentials, network error, timeout, etc.)
}
```

`$response` is the raw decoded JSON object returned by HUAWEI's push API; inspect its `code`/`msg` fields to confirm whether the push actually succeeded.
