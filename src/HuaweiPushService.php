<?php

namespace Innoractive\HuaweiPushService;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class HuaweiPushService
{
    /**
     * Get the access token.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getAccessToken(string $clientId, string $clientSecret, ?ClientInterface $client = null): string
    {
        $response = ($client ?? new Client)->request('POST', 'https://oauth-login.cloud.huawei.com/oauth2/v3/token', [
            'timeout' => 30,
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents());

        return $data->access_token ?? '';
    }

    /**
     * Send push notification.
     *
     * @throws \InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendNotification(
        string $clientId,
        string $accessToken,
        string $title,
        string $body,
        mixed $tokens,
        array $action = ['type' => 3], // Type 3 = IGNORE
        ?ClientInterface $client = null
    ): object {
        if (! is_array($tokens)) {
            $tokens = [$tokens];
        }

        if (empty($tokens)) {
            throw new \InvalidArgumentException('At least one device token must be provided.');
        }

        $payload = json_encode([
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

        $response = ($client ?? new Client)->request('POST', 'https://push-api.cloud.huawei.com/v1/'.rawurlencode($clientId).'/messages:send', [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf8',
            ],
            'connect_timeout' => 30,
            'timeout' => 30,
            'body' => $payload,
        ]);

        return json_decode($response->getBody()->getContents()) ?? (object) [];
    }
}
