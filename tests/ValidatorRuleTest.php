<?php

namespace Krynox\Captcha\Tests;

use Illuminate\Support\Facades\Validator;

/** The string-rule alias registered by the service provider: 'krynox-captcha' => 'required|krynox'. */
class ValidatorRuleTest extends TestCase
{
    public function test_passes_with_a_valid_token_and_sends_the_full_verify_payload(): void
    {
        $this->bindRequest(['krynox-captcha' => 'valid-token', 'krynox-hp' => ''], '203.0.113.9');

        $validator = Validator::make(
            ['krynox-captcha' => 'valid-token'],
            ['krynox-captcha' => ['required', 'krynox']]
        );

        $this->assertTrue($validator->passes());

        $requests = self::$plane->requests();
        $this->assertCount(1, $requests);
        $this->assertSame('POST', $requests[0]['method']);
        $this->assertSame('/siteverify', $requests[0]['path']);

        $body = $requests[0]['body'];
        $this->assertSame('kcps_test_secret', $body['secret']);
        $this->assertSame('valid-token', $body['response']);
        $this->assertSame('203.0.113.9', $body['remoteip']);
        $this->assertSame('', $body['honeypot']);
        // retries (2) > 0 → every verify carries an idempotency key.
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $body['idempotency_key']);
    }

    public function test_fails_with_an_invalid_token_and_reports_the_rule_message(): void
    {
        $this->bindRequest(['krynox-captcha' => 'bad-token', 'krynox-hp' => ''], '203.0.113.9');

        $validator = Validator::make(
            ['krynox-captcha' => 'bad-token'],
            ['krynox-captcha' => ['required', 'krynox']]
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'The krynox-captcha could not be verified. Please try again.',
            $validator->errors()->first('krynox-captcha')
        );

        $requests = self::$plane->requests();
        $this->assertCount(1, $requests);
        $this->assertSame('bad-token', $requests[0]['body']['response']);
    }
}
