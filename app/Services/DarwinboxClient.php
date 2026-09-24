<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The Darwinbox side of the SSO handshake. Darwinbox redirects here with a
 * payload carrying the employee's email and session token; the token is only
 * worth anything once Darwinbox confirms it, and the email is only worth
 * anything once Darwinbox confirms the token belongs to it.
 */
class DarwinboxClient
{
    /**
     * Where checkToken may put the identity of the token's owner. Darwinbox
     * has not documented the shape, so the first one present wins.
     */
    private const IDENTITY_KEYS = [
        'email',
        'official_email',
        'data.email',
        'data.official_email',
        'user.email',
    ];

    /**
     * Decode the `data` query parameter Darwinbox sends:
     * base64( xor( base64(json), key ) ). Obfuscation only -- nothing in it is
     * trusted until verifiedEmail() has checked it against Darwinbox.
     *
     * @return array{email: string, token: string}|null
     */
    public function decodePayload(?string $data): ?array
    {
        if (! $data) {
            return null;
        }

        $key = (string) config('services.darwinbox.payload_key');
        // Non-strict, as before: Darwinbox's encoding has been accepted this way.
        $xored = base64_decode($data);

        if ($xored === false || $key === '') {
            return null;
        }

        $plain = '';
        for ($i = 0, $n = strlen($xored), $k = strlen($key); $i < $n; $i++) {
            $plain .= $xored[$i] ^ $key[$i % $k];
        }

        $payload = json_decode((string) base64_decode($plain), true);

        if (! is_string($payload['email'] ?? null) || ! is_string($payload['token'] ?? null)) {
            return null;
        }

        return ['email' => $payload['email'], 'token' => $payload['token']];
    }

    /**
     * Raw checkToken response, or null when Darwinbox could not be reached or
     * did not answer with JSON.
     *
     * @return array<string, mixed>|null
     */
    public function checkToken(string $token): ?array
    {
        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders(['Authorization' => 'Basic '.config('services.darwinbox.basic_auth')])
                ->post(config('services.darwinbox.check_token_url'), [
                    'api_key' => config('services.darwinbox.api_key'),
                    'token' => $token,
                ]);
        } catch (Throwable $e) {
            Log::warning('Darwinbox checkToken unreachable', ['error' => $e->getMessage()]);

            return null;
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    }

    /**
     * The email the SSO login may act as, or null to refuse the login.
     *
     * The payload email is caller-supplied, so on its own it proves nothing:
     * it is accepted only when checkToken says the token is live *and* names
     * the same person. If the response carries no identity at all the login
     * is refused unless services.darwinbox.enforce_identity has been switched
     * off -- the keys are logged (never the values) so the right field can be
     * added to IDENTITY_KEYS.
     */
    public function verifiedEmail(string $email, string $token): ?string
    {
        $response = $this->checkToken($token);

        if (! $response || (int) ($response['status'] ?? 0) !== 1) {
            return null;
        }

        $owner = collect(self::IDENTITY_KEYS)
            ->map(fn ($key) => Arr::get($response, $key))
            ->first(fn ($value) => is_string($value) && $value !== '');

        if ($owner === null) {
            Log::warning('Darwinbox checkToken returned no identity', [
                'keys' => array_keys(Arr::dot($response)),
            ]);

            return config('services.darwinbox.enforce_identity') ? null : $email;
        }

        if (strcasecmp(trim($owner), trim($email)) !== 0) {
            Log::warning('Darwinbox SSO email does not match token owner', ['claimed' => $email]);

            return null;
        }

        return $email;
    }
}
