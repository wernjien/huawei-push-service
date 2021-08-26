<?php

namespace Innoractive\HuaweiPushService;

use GuzzleHttp\Client;

class HuaweiPushService
{
    /**
     * Get the access token.
     *
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @return string
     */
    public static function getAccessToken($clientId, $clientSecret)
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
     *
     * @param  string  $clientId
     * @param  string  $accessToken
     * @param  string  $title
     * @param  string  $body
     * @param  mixed  $tokens
     * @param  string  $intent
     * @return object
     */
    public static function sendNotification($clientId, $accessToken, $title, $body, $tokens, $intent = '')
    {
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
                        'click_action' => [
                            'type' => 1,
                            'intent' => $intent,
                        ],
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
