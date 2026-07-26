<?php

namespace Krynox\Captcha\Tests;

use Krynox\Captcha\KrynoxCaptcha;

class FeedbackTest extends TestCase
{
    public function test_feedback_posts_label_ip_and_note_to_the_feedback_endpoint(): void
    {
        $ok = $this->app->make(KrynoxCaptcha::class)
            ->feedback('human', '203.0.113.9', 'false positive — unblock');

        $this->assertTrue($ok);

        $requests = self::$plane->requests();
        $this->assertCount(1, $requests);
        $this->assertSame('POST', $requests[0]['method']);
        $this->assertSame('/feedback', $requests[0]['path']);
        $this->assertSame([
            'secret' => 'kcps_test_secret',
            'label' => 'human',
            'ip' => '203.0.113.9',
            'note' => 'false positive — unblock',
        ], $requests[0]['body']);
    }
}
