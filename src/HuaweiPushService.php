<?php

namespace Innoractive\HuaweiPushService;

use GuzzleHttp\Client;
use Innoractive\HuaweiPushService\Support\Singleton;

class HuaweiPushService
{

    use Singleton;

    /**
     * client id.
     *
     * @var string
     */
    protected $client_id;

    /**
     * client secret.
     *
     * @var string
     */
    protected $client_secret;

    /**
     * grant type.
     *
     * @var string
     */
    protected $grant_type;


    public static function __callStatic($method, $args)
    {
        return call_user_func_array([static::getInstance(), $method], $args);
    }

    public function __construct($client_id = '', $client_secret = '', $grant_type = '')
    {
        $this->init($client_id, $client_secret, $grant_type);
    }

     /**
     * Initialize the config on a HuaweiPushService instance.
     *
     * @param  string  $grant_type
     * @param  string  $client_id
     * @param  string  $client_secret
     * @return void
     */
    protected function init($grant_type = '', $client_id = '', $client_secret = '')
    {
        $this->grant_type = $grant_type ?: static::getConfig('grant_type');
        $this->client_id = $client_id ?: static::getConfig('client_id');
        $this->client_secret = $client_secret ?: static::getConfig('client_secret');
    }

    /**
     * Get the config for the given key.
     *
     * @param  string  $key
     * @return string
     */
    protected function getConfig($key)
    {
        if (function_exists('config')) {
            return config('push-setting.'.$key);
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
    protected function getAccessToken($grant_type,$client_id,$client_secret)
    {
        $client = new Client();
        $response = $client->request('POST', "https://oauth-login.cloud.huawei.com/oauth2/v3/token", [
            
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded'
            ],
            'body' => 'grant_type='.$grant_type.'&client_id='.$client_id.'&client_secret='.$client_secret.''
        ]);

        $result = $response->getBody()->getContents();

        return json_decode($result,true);
    }

    /**
     * Sent the notification.
     *
     * @return array
     */
    protected function sendNotification($title,$body,$click_action,$token_device)
    {
        $generate = static::getAccessToken($this->grant_type,$this->client_id,$this->client_secret);

        return static::sendMessageNotification($generate['access_token'],$title,$body,$click_action,$token_device);
    }

    /**
     * notification parameters and sent to huawei API.
     *
     * @return array
     */
    protected function sendMessageNotification($token,$title,$body,$click_action,$token_device)
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
                            'intent'=>$click_action
                        ]
                    ]
                ],
                'token'=>[$token_device]
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
