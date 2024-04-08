<?php

namespace Propaganistas\LaravelSms;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use Propaganistas\LaravelSms\Notifications\SmsChannel;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sms');

        $this->app->singleton('sms.manager', function ($app) {
            return new SmsManager(
                container: $app,
                config: $app['config']
            );
        });

        $this->app->alias('sms.manager', SmsManager::class);

        $this->app[ChannelManager::class]->extend('sms', function ($app) {
            return $app->make(SmsChannel::class);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => $this->app->configPath('sms.php'),
        ], 'config');
    }
}
