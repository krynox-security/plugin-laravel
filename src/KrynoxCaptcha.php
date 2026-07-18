<?php

namespace Krynox\Captcha;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Server-side verification client for Krynox Captcha. Mirrors the official SDKs.
 */
class KrynoxCaptcha
{
    public function __construct(
        protected string $secret,
        protected string $apiHost = 'https://api.krynox.net',
        protected int $timeout = 5,
        protected int $retries = 2,
    ) {
    }

    /**
     * Verify a solved token from the widget.
     *
     * @return array{success:bool,score:?float,risk:?string,hostname:?string,challenge_ts:?string,error_codes:array<int,string>,reasons:array<int,string>,agent:?array{verified:bool,name:?string,allowlisted:bool},human:?array{attested:bool,method:?string,issuer:?string}}
     */
    public function verify(?string $response, ?string $remoteip = null): array
    {
        if (empty($response)) {
            return $this->fail(['missing-input-response']);
        }

        // A token is single-use, so a retried verify carries an idempotency key — the server returns
        // the first outcome instead of failing the now-consumed token.
        $key = $this->retries > 0 ? bin2hex(random_bytes(16)) : null;

        try {
            $data = $this->client()
                ->post(rtrim($this->apiHost, '/').'/siteverify', [
                    'secret' => $this->secret,
                    'response' => $response,
                    'remoteip' => $remoteip,
                    'idempotency_key' => $key,
                ])
                ->json();
        } catch (Throwable $e) {
            return $this->fail(['request-failed']);
        }

        if (! is_array($data)) {
            return $this->fail(['request-failed']);
        }

        $agent = is_array($data['agent'] ?? null) ? $data['agent'] : null;
        $human = is_array($data['human'] ?? null) ? $data['human'] : null;

        return [
            'success' => ($data['success'] ?? false) === true,
            'score' => $data['score'] ?? null,
            'risk' => $data['risk'] ?? null,
            'hostname' => $data['hostname'] ?? null,
            'challenge_ts' => $data['challenge_ts'] ?? null,
            'error_codes' => $data['error-codes'] ?? [],
            'reasons' => $data['reasons'] ?? [],
            'agent' => $agent === null ? null : [
                'verified' => ($agent['verified'] ?? false) === true,
                'name' => $agent['name'] ?? null,
                'allowlisted' => ($agent['allowlisted'] ?? false) === true,
            ],
            'human' => $human === null ? null : [
                'attested' => ($human['attested'] ?? false) === true,
                'method' => $human['method'] ?? null,
                'issuer' => $human['issuer'] ?? null,
            ],
        ];
    }

    /**
     * Report detection-quality feedback ("human" | "bot"). Flagging an auto-blocked
     * IP as "human" un-blocks it server-side (false-positive correction).
     */
    public function feedback(string $label, ?string $ip = null, ?string $note = null): bool
    {
        try {
            return ($this->client()
                ->post(rtrim($this->apiHost, '/').'/feedback', [
                    'secret' => $this->secret,
                    'label' => $label,
                    'ip' => $ip,
                    'note' => $note,
                ])
                ->json('ok') ?? false) === true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * A configured HTTP client that retries transient failures — connection errors, 429, and 5xx —
     * with exponential-ish backoff, but never a 4xx (e.g. an invalid token). Does not throw on the
     * final failed response; callers inspect the parsed body.
     */
    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($this->timeout)
            ->acceptJson()
            ->retry($this->retries + 1, 100, function (Throwable $e): bool {
                if ($e instanceof ConnectionException) {
                    return true;
                }
                if ($e instanceof RequestException && $e->response !== null) {
                    $status = $e->response->status();

                    return $status === 429 || $status >= 500;
                }

                return false;
            }, throw: false);
    }

    /**
     * @param  array<int,string>  $codes
     * @return array{success:bool,score:null,risk:null,hostname:null,challenge_ts:null,error_codes:array<int,string>,reasons:array<int,string>,agent:null,human:null}
     */
    private function fail(array $codes): array
    {
        return [
            'success' => false,
            'score' => null,
            'risk' => null,
            'hostname' => null,
            'challenge_ts' => null,
            'error_codes' => $codes,
            'reasons' => [],
            'agent' => null,
            'human' => null,
        ];
    }
}
