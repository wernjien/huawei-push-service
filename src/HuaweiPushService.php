<?php

namespace Innoractive\HuaweiPushService;

use GuzzleHttp\Client;

class HuaweiPushService
{

    /**
     * To get access token before sending notification.
     *
     * @return array
     */
    public static function getAccessToken()
    {
        $client = new Client();
        $response = $client->request('POST', "https://oauth-login.cloud.huawei.com/oauth2/v3/token", [
            
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded'
            ],
            'connect_timeout' => 30,
            'body' => 'grant_type=client_credentials&client_id=102714235&client_secret=0a36c9ab8a72c722dfffcc79c0892a2f3bbcabdb1b4691f01d12aa57ad2be225'
        ]);

        $result = $response->getBody()->getContents();

        return json_decode($result,true);
    }

    /**
     * Sent the notification.
     *
     * @return array
     */
    public static function sendNotification($title,$body,$deviceToken)
    {
        
        $generate = self::getAccessToken();

        return self::sendMessageNotification($generate['access_token'],$title,$body,$deviceToken);

    }

    /**
     * notification paramters and sent to huawei API.
     *
     * @return array
     */
    public static function sendMessageNotification($token,$title,$body,$deviceToken)
    {
        $param = [
            'validate_only'=>false,
            'message'=>[
                'notification'=>[
                    'title'=>$title,
                    'body'=>$body
                ],
                'android'=>[
                    'notification'=>[
                        'title'=>$title,
                        'body'=>$body,
                        'click_action'=>[
                            'type'=>1,
                            'intent'=>'intent://com.huawei.codelabpush/deeplink?#Intent;scheme=pushscheme;launchFlags=0x04000000;i.age=180;S.name=abc;end'
                        ]
                    ]
                ],
                'token'=>[$deviceToken]
            ]
        ];

        $param = json_encode($param);

        $request = new Client();
        $response = $request->request('POST', "https://push-api.cloud.huawei.com/v1/102714235/messages:send", [
            'headers' => [
                "Authorization" => "Bearer ".$token,
                "Accept" => "application/json",
                "content-type" => "application/json; charset=utf8"
            ],
            'connect_timeout' => 30,
            'body' => $param
        ]);

        return json_decode($response->getBody()->getContents(),true);
    }
}
