<?php

namespace Krynox\Captcha\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Blade component: <x-krynox-captcha /> (optionally <x-krynox-captcha sitekey="kcpt_…" />).
 */
class Widget extends Component
{
    public string $challenge;

    public string $cdn;

    public function __construct(?string $sitekey = null)
    {
        $key = $sitekey ?: (string) config('krynox.site_key');
        $api = rtrim((string) config('krynox.api_host', 'https://api.krynox.net'), '/');
        $this->cdn = rtrim((string) config('krynox.cdn_host', 'https://cdn.krynox.net'), '/');
        $this->challenge = $api.'/challenge?sitekey='.rawurlencode($key);
    }

    public function render(): View
    {
        return view('krynox::widget');
    }
}
