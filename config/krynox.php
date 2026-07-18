<?php

return [
    // Public site key (kcpt_…) used by the widget.
    'site_key' => env('KRYNOX_SITE_KEY', ''),

    // Server-side secret key (kcps_…) used to verify solutions. Keep private.
    'secret_key' => env('KRYNOX_SECRET_KEY', ''),

    // Data-plane host (verify + challenge). Override for self-hosting.
    'api_host' => env('KRYNOX_API_HOST', 'https://api.krynox.net'),

    // CDN host serving the widget script.
    'cdn_host' => env('KRYNOX_CDN_HOST', 'https://cdn.krynox.net'),

    // Verify request timeout (seconds).
    'timeout' => (int) env('KRYNOX_TIMEOUT', 5),

    // Transient-failure (network / 429 / 5xx) retries. A retried single-use token replays the
    // first outcome via an idempotency key.
    'retries' => (int) env('KRYNOX_RETRIES', 2),
];
