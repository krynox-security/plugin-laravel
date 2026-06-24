<?php

namespace Krynox\Captcha;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Server-side verification client for Krynox Captcha. Mirrors the official SDKs.
 */
class KrynoxCaptcha
{
    public function __construct(
        protected string $secret,
        protected string $apiHost = 'https://api.krynox.id',
        protected int $timeout = 5,
    ) {
    }

    /**
     * Verify a solved token from the widget.
     *
     * @return array{success:bool,score:?float,risk:?string,hostname:?string,challenge_ts:?string,error_codes:array<int,string>}
     */
    public function verify(?string $response, ?string $remoteip = null): array
    {
        if (empty($response)) {
            return $this->fail(['missing-input-response']);
        }

        try {
            $data = Http::timeout($this->timeout)
                ->acceptJson()
                ->post(rtrim($this->apiHost, '/').'/siteverify', [
                    'secret' => $this->secret,
                    'response' => $response,
                    'remoteip' => $remoteip,
                ])
                ->json();
        } catch (Throwable $e) {
            return $this->fail(['request-failed']);
        }

        if (! is_array($data)) {
            return $this->fail(['request-failed']);
        }

        return [
            'success' => ($data['success'] ?? false) === true,
            'score' => $data['score'] ?? null,
            'risk' => $data['risk'] ?? null,
            'hostname' => $data['hostname'] ?? null,
            'challenge_ts' => $data['challenge_ts'] ?? null,
            'error_codes' => $data['error-codes'] ?? [],
        ];
    }

    /**
     * Report detection-quality feedback ("human" | "bot"). Flagging an auto-blocked
     * IP as "human" un-blocks it server-side (false-positive correction).
     */
    public function feedback(string $label, ?string $ip = null, ?string $note = null): bool
    {
        try {
            return (Http::timeout($this->timeout)
                ->acceptJson()
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
     * @param  array<int,string>  $codes
     * @return array{success:bool,score:null,risk:null,hostname:null,challenge_ts:null,error_codes:array<int,string>}
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
        ];
    }
}
