<?php

namespace Innoractive\HuaweiPushService;

use GuzzleHttp\Client;

class HuaweiPushService
{
    /**
     * Get the config for the given key.
     *
     * @param  string  $key
     * @return string
     */
    public static function getConfig($key)
    {
        if (function_exists('config')) {
            return config('huawei-push-service.'.$key);
        }

        if (function_exists('getenv')) {
            return getenv('HUAWEI_.'.strtoupper($key));
        }

        return '';
    }

    /**
     * To get access token before sending notification.
     *
     * @return array
     */
    public static function getAccessToken()
    {
        $grantType = self::getConfig('grant_type');
        $clientId = self::getConfig('client_id');
        $clientSecret = self::getConfig('client_secret');

        $client = new Client();
        $response = $client->request('POST', 'https://oauth-login.cloud.huawei.com/oauth2/v3/token', [
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            'body' => 'grant_type='.$grantType.'&client_id='.$clientId.'&client_secret='.$clientSecret.'',
        ]);

        $result = $response->getBody()->getContents();

        return json_decode($result, true);
    }

    /**
     * Sent the notification.
     *
     * @return array
     */
    public static function sendNotification($title, $body, $clickAction, $tokenDevice)
    {
        $generate = self::getAccessToken();

        return self::sendMessageNotification($generate['access_token'], $title, $body, $clickAction, $tokenDevice);
    }

    /**
     * notification parameters and sent to huawei API.
     *
     * @return array
     */
    public static function sendMessageNotification($token, $title, $body, $clickAction, $tokenDevice)
    {
        $param = [
            'validate_only' => false,
            'message' => [
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'android' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'click_action' => [
                            'type'   => 1,
                            'intent' => $clickAction,
                        ],
                    ],
                ],
                'token' => [$tokenDevice],
            ],
        ];

        $param = json_encode($param);

        $request = new Client();
        $response = $request->request('POST', 'https://push-api.cloud.huawei.com/v1/102714235/messages:send', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
                'content-type'  => 'application/json; charset=utf8',
            ],
            'connect_timeout' => 30,
            'body' => $param,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
