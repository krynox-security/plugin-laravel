# Krynox Captcha for Laravel

Privacy-first, proof-of-work CAPTCHA for Laravel — a **Blade component** to render the
widget and a **validation rule** to verify it. No cookies, no puzzles.

## Install

```bash
composer require krynox/captcha-laravel
```

The package auto-registers (Laravel package discovery). Set your keys in `.env`:

```env
KRYNOX_SITE_KEY=kcpt_live_xxx
KRYNOX_SECRET_KEY=kcps_live_xxx
```

(Optional) publish the config: `php artisan vendor:publish --tag=krynox-config`.

## Render the widget

```blade
<form method="POST" action="/register">
    @csrf
    <input name="email" type="email" required>

    <x-krynox-captcha />

    <button type="submit">Create account</button>
</form>
```

The widget submits its solution as a hidden `krynox-captcha` field.

## Verify the submission

Use the string rule:

```php
$request->validate([
    'krynox-captcha' => 'required|krynox',
]);
```

…or the rule object:

```php
use Krynox\Captcha\Rules\Krynox;

$request->validate([
    'krynox-captcha' => ['required', new Krynox],
]);
```

Or verify manually anywhere:

```php
use Krynox\Captcha\KrynoxCaptcha;

$result = app(KrynoxCaptcha::class)->verify($request->input('krynox-captcha'), $request->ip());
if (! $result['success']) {
    abort(422, 'Captcha failed');
}
// $result['risk'] => 'low' | 'medium' | 'high'
// $result['reasons'] => ['tor-exit', ...]; $result['agent']; $result['human']
```

The result array carries the full contract: `success`, `score`, `risk`, `hostname`, `challenge_ts`,
`error_codes`, `reasons`, `agent` (`{verified,name,allowlisted}` or null), `human`
(`{attested,method,issuer}` or null). Transient failures (network / 429 / 5xx) are retried
automatically with a per-verify idempotency key.

### Feedback (false-positive correction)

```php
app(KrynoxCaptcha::class)->feedback('human', $request->ip(), 'support ticket #1234');
```

## Config

`config/krynox.php`: `site_key`, `secret_key`, `api_host`, `cdn_host`, `timeout`, `retries`
(override `api_host` / `cdn_host` for self-hosting).

## Honeypot

Enable **Honeypot** for the site in the Krynox dashboard and the widget injects an invisible decoy
field (`krynox-hp`) that only bots fill in. The `krynox` validation rule forwards it to `/siteverify`
as `honeypot` automatically — no code change needed (a manual
`KrynoxCaptcha::verify($token, $ip, $request->input('krynox-hp'))` call works too). The data plane
then floors the score (report mode) or rejects with `honeypot-tripped` (enforce mode). See the
[Honeypot docs](https://docs.krynox.net/server-side/honeypot/).

## License

MIT. Built for [Krynox Captcha](https://krynox.net) · docs: <https://docs.krynox.net>
