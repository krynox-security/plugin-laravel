<?php

return [
    // Public site key (kcpt_…) used by the widget.
    'site_key' => env('KRYNOX_SITE_KEY', ''),

    // Server-side secret key (kcps_…) used to verify solutions. Keep private.
    'secret_key' => env('KRYNOX_SECRET_KEY', ''),

    // Data-plane host (verify + challenge). Override for self-hosting.
    'api_host' => env('KRYNOX_API_HOST', 'https://api.krynox.id'),

    // CDN host serving the widget script.
    'cdn_host' => env('KRYNOX_CDN_HOST', 'https://cdn.krynox.id'),

    // Verify request timeout (seconds).
    'timeout' => (int) env('KRYNOX_TIMEOUT', 5),
];
