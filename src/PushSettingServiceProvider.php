<?php

namespace Innoractive\HuaweiPushService;

use Illuminate\Support\ServiceProvider;

class PushSettingServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/push-setting.php' => config_path('push-setting.php'),
        ], 'config');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register() {}
}
