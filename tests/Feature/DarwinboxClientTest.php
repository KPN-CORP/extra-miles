<?php

namespace Tests\Feature;

use App\Services\DarwinboxClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DarwinboxClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.darwinbox.check_token_url' => 'https://darwinbox.test/checkToken',
            'services.darwinbox.payload_key' => '666666',
            'services.darwinbox.enforce_identity' => true,
        ]);
    }

    private function encode(array $payload): string
    {
        $plain = base64_encode(json_encode($payload));
        $key = '666666';
        $xored = '';
        for ($i = 0; $i < strlen($plain); $i++) {
            $xored .= $plain[$i] ^ $key[$i % strlen($key)];
        }

        return base64_encode($xored);
    }

    public function test_decodes_the_darwinbox_payload(): void
    {
        $data = $this->encode(['email' => 'a@kpn-corp.com', 'token' => 't1']);

        $this->assertSame(
            ['email' => 'a@kpn-corp.com', 'token' => 't1'],
            (new DarwinboxClient)->decodePayload($data)
        );
    }

    public function test_rejects_garbage_payloads(): void
    {
        $client = new DarwinboxClient;

        $this->assertNull($client->decodePayload(null));
        $this->assertNull($client->decodePayload('not-a-payload'));
        $this->assertNull($client->decodePayload($this->encode(['email' => 'a@kpn-corp.com'])));
    }

    public function test_accepts_email_matching_token_owner(): void
    {
        Http::fake(['*' => Http::response(['status' => 1, 'email' => 'A@kpn-corp.com'])]);

        $this->assertSame('a@kpn-corp.com', (new DarwinboxClient)->verifiedEmail('a@kpn-corp.com', 't1'));
    }

    public function test_refuses_someone_elses_email_on_a_valid_token(): void
    {
        Http::fake(['*' => Http::response(['status' => 1, 'data' => ['email' => 'me@kpn-corp.com']])]);

        $this->assertNull((new DarwinboxClient)->verifiedEmail('superadmin@kpn-corp.com', 't1'));
    }

    public function test_refuses_invalid_token(): void
    {
        Http::fake(['*' => Http::response(['status' => 0])]);

        $this->assertNull((new DarwinboxClient)->verifiedEmail('a@kpn-corp.com', 't1'));
    }

    public function test_refuses_when_response_has_no_identity_unless_enforcement_is_off(): void
    {
        Http::fake(['*' => Http::response(['status' => 1])]);

        $this->assertNull((new DarwinboxClient)->verifiedEmail('a@kpn-corp.com', 't1'));

        config(['services.darwinbox.enforce_identity' => false]);

        $this->assertSame('a@kpn-corp.com', (new DarwinboxClient)->verifiedEmail('a@kpn-corp.com', 't1'));
    }

    public function test_refuses_when_darwinbox_is_unreachable(): void
    {
        Http::fake(['*' => Http::response('<html>502</html>', 502)]);

        $this->assertNull((new DarwinboxClient)->verifiedEmail('a@kpn-corp.com', 't1'));
    }
}
