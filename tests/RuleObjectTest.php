<?php

namespace Krynox\Captcha\Tests;

use Illuminate\Support\Facades\Validator;
use Krynox\Captcha\Rules\Krynox;

/** The invokable rule object: 'krynox-captcha' => ['required', new Krynox]. */
class RuleObjectTest extends TestCase
{
    public function test_passes_with_a_valid_token(): void
    {
        $this->bindRequest(['krynox-captcha' => 'valid-token', 'krynox-hp' => 'hp-decoy'], '198.51.100.7');

        $validator = Validator::make(
            ['krynox-captcha' => 'valid-token'],
            ['krynox-captcha' => ['required', new Krynox]]
        );

        $this->assertTrue($validator->passes());

        $requests = self::$plane->requests();
        $this->assertCount(1, $requests);
        $this->assertSame('/siteverify', $requests[0]['path']);

        $body = $requests[0]['body'];
        $this->assertSame('kcps_test_secret', $body['secret']);
        $this->assertSame('valid-token', $body['response']);
        $this->assertSame('198.51.100.7', $body['remoteip']);
        $this->assertSame('hp-decoy', $body['honeypot']);
    }

    public function test_fails_with_an_invalid_token(): void
    {
        $this->bindRequest(['krynox-captcha' => 'bad-token'], '198.51.100.7');

        $validator = Validator::make(
            ['krynox-captcha' => 'bad-token'],
            ['krynox-captcha' => ['required', new Krynox]]
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'The krynox-captcha could not be verified. Please try again.',
            $validator->errors()->first('krynox-captcha')
        );
    }
}
