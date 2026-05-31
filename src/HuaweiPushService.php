<?php

namespace Innoractive\HuaweiPushService;

use GuzzleHttp\Client;

class HuaweiPushService
{
    /**
     * Get the access token.
     */
    public static function getAccessToken(string $clientId, string $clientSecret): string
    {
        $response = (new Client)->request('POST', 'https://oauth-login.cloud.huawei.com/oauth2/v3/token', [
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
            ],

            'body' => "grant_type=client_credentials&client_id={$clientId}&client_secret={$clientSecret}",
        ]);

        $data = json_decode($response->getBody()->getContents());
        $accessToken = isset($data->access_token) ? $data->access_token : '';

        return $accessToken;
    }

    /**
     * Send push notification.
     */
    public static function sendNotification(
        string $clientId,
        string $accessToken,
        string $title,
        string $body,
        mixed $tokens,
        array $action = ['type' => 3] // Type 3 = IGNORE
    ): object {
        if (! is_array($tokens)) {
            $tokens = [$tokens];
        }

        $body = json_encode([
            'validate_only' => false,
            'message' => [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'android' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'click_action' => $action,
                    ],
                ],
                'token' => $tokens,
            ],
        ]);

        $response = (new Client)->request('POST', "https://push-api.cloud.huawei.com/v1/{$clientId}/messages:send", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf8',
            ],
            'connect_timeout' => 30,
            'body' => $body,
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
