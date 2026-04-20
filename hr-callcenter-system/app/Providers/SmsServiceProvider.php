<?php

namespace App\Providers;

use App\Services\Sms\SmsManager;
use App\Services\Sms\ViolatorNotifier;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/sms.php', 'sms');

        $this->app->singleton(SmsManager::class, function ($app) {
            return new SmsManager($app, $app['config']->get('sms', []));
        });

        $this->app->bind(ViolatorNotifier::class, function ($app) {
            return new ViolatorNotifier($app->make(SmsManager::class));
        });
    }

    public function boot(): void
    {
    }
}
