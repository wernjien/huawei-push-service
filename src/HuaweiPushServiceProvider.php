<?php

namespace Innoractive\HuaweiPushService;

use Illuminate\Support\ServiceProvider;

class HuaweiPushServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/huawei-push-service.php' => config_path('huawei-push-service.php'),
        ], 'config');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register() {}
}
