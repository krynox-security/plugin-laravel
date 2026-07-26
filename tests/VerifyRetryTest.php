<?php

namespace Krynox\Captcha\Tests;

use Krynox\Captcha\KrynoxCaptcha;

class VerifyRetryTest extends TestCase
{
    public function test_a_transient_500_is_retried_once_with_the_same_idempotency_key(): void
    {
        self::$plane->queue([
            ['status' => 500, 'body' => ['error' => 'boom']],
            ['status' => 200, 'body' => [
                'success' => true,
                'score' => 0.05,
                'risk' => 'low',
                'hostname' => 'example.test',
                'challenge_ts' => '2026-07-27T00:00:00Z',
                'error-codes' => [],
                'reasons' => [],
            ]],
        ]);

        $result = $this->app->make(KrynoxCaptcha::class)
            ->verify('valid-token', '203.0.113.9', '');

        $this->assertTrue($result['success']);
        $this->assertSame([], $result['error_codes']);

        $requests = self::$plane->requests();
        $this->assertCount(2, $requests, 'expected exactly 2 hits: the 500 and the retried 200');
        $this->assertSame('/siteverify', $requests[0]['path']);
        $this->assertSame('/siteverify', $requests[1]['path']);

        $key = $requests[0]['body']['idempotency_key'];
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $key);
        $this->assertSame($key, $requests[1]['body']['idempotency_key'], 'retry must replay the same idempotency key');
        $this->assertSame($requests[0]['body'], $requests[1]['body'], 'retry must resend an identical payload');
    }

    public function test_a_4xx_invalid_token_is_not_retried(): void
    {
        $result = $this->app->make(KrynoxCaptcha::class)
            ->verify('bad-token', '203.0.113.9', '');

        $this->assertFalse($result['success']);
        $this->assertSame(['invalid-input-response'], $result['error_codes']);
        $this->assertCount(1, self::$plane->requests());
    }
}
