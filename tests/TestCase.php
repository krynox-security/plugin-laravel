<?php

namespace Krynox\Captcha\Tests;

use Illuminate\Http\Request;
use Krynox\Captcha\KrynoxServiceProvider;
use Krynox\Captcha\Tests\Support\MockPlane;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected static ?MockPlane $plane = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$plane ??= MockPlane::start();
    }

    public static function tearDownAfterClass(): void
    {
        self::$plane?->stop();
        self::$plane = null;
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::$plane->reset();
    }

    protected function getPackageProviders($app): array
    {
        return [KrynoxServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('krynox', [
            'site_key' => 'kcpt_test_site',
            'secret_key' => 'kcps_test_secret',
            'api_host' => self::$plane->url,
            'cdn_host' => 'https://cdn.example.test',
            'timeout' => 5,
            'retries' => 2,
        ]);
    }

    /** Bind an incoming HTTP request so request()->ip() / ->input() are deterministic. */
    protected function bindRequest(array $post, string $ip = '203.0.113.9'): void
    {
        $request = Request::create('http://localhost/login', 'POST', $post, [], [], ['REMOTE_ADDR' => $ip]);
        $this->app->instance('request', $request);
    }
}
