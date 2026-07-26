<?php

namespace Krynox\Captcha\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;

class WidgetComponentTest extends TestCase
{
    use InteractsWithViews;

    public function test_renders_the_cdn_script_and_the_widget_element_with_the_configured_sitekey(): void
    {
        $view = $this->blade('<x-krynox-captcha />');

        $view->assertSee('src="https://cdn.example.test/widget/krynox-captcha.js"', false);
        $view->assertSee(
            '<krynox-captcha challenge="'.self::$plane->url.'/challenge?sitekey=kcpt_test_site"></krynox-captcha>',
            false
        );
    }

    public function test_an_explicit_sitekey_attribute_overrides_the_configured_one(): void
    {
        $view = $this->blade('<x-krynox-captcha sitekey="kcpt_other" />');

        $view->assertSee('challenge?sitekey=kcpt_other', false);
    }
}
