<?php

namespace Krynox\Captcha;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Krynox\Captcha\View\Components\Widget;

class KrynoxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/krynox.php', 'krynox');

        $this->app->singleton(KrynoxCaptcha::class, function ($app) {
            $c = $app['config']['krynox'];

            return new KrynoxCaptcha(
                (string) ($c['secret_key'] ?? ''),
                (string) ($c['api_host'] ?? 'https://api.krynox.net'),
                (int) ($c['timeout'] ?? 5),
                (int) ($c['retries'] ?? 2),
            );
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'krynox');

        $this->publishes([
            __DIR__.'/../config/krynox.php' => config_path('krynox.php'),
        ], 'krynox-config');

        // <x-krynox-captcha /> Blade component.
        Blade::component('krynox-captcha', Widget::class);

        // String-rule alias so you can write: 'krynox-captcha' => 'required|krynox'
        Validator::extend('krynox', function ($attribute, $value, $parameters, $validator) {
            return app(KrynoxCaptcha::class)->verify(
                is_string($value) ? $value : null,
                request()->ip(),
                request()->input('krynox-hp')
            )['success'];
        }, 'The :attribute could not be verified. Please try again.');
    }
}
