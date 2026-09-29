<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Services\DarwinboxClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Darwinbox SSO entry points. `dbauth` logs the employee into this app's
 * admin; the others verify them the same way and hand them on to a partner
 * system with a signed JWT (see services.sso_vendors).
 */
class SsoController extends Controller
{
    public function __construct(private DarwinboxClient $darwinbox) {}

    public function dbauth(Request $request)
    {
        [$user, $token] = $this->verify($request);

        if (! $user) {
            return $this->failed();
        }

        Auth::login($user);
        $user->token = $token;
        $user->email_log = $user->email;
        $user->save();
        $request->session()->put('system', 'kpnem');
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    public function dbauthlms(Request $request)
    {
        return $this->handOff($request, 'lms');
    }

    public function dbauthcmpr(Request $request)
    {
        return $this->handOff($request, 'cmpr');
    }

    public function dbauthexpl(Request $request)
    {
        return $this->handOff($request, 'expl');
    }

    /**
     * The user Darwinbox vouches for and their Darwinbox token, or nulls. The
     * payload email is only trusted once DarwinboxClient has matched it to
     * the token's owner.
     *
     * @return array{0: ?User, 1: ?string}
     */
    private function verify(Request $request): array
    {
        $payload = $this->darwinbox->decodePayload($request->input('data'));

        if (! $payload) {
            // Shapes only, never values: enough to tell a missing parameter,
            // an unset DARWINBOX_PAYLOAD_KEY and a wrong key apart.
            Log::warning('SSO payload missing or undecodable', [
                'path' => $request->path(),
                'has_data' => $request->filled('data'),
                'data_length' => strlen((string) $request->input('data')),
                'payload_key_configured' => (string) config('services.darwinbox.payload_key') !== '',
                'config_cached' => app()->configurationIsCached(),
            ]);

            return [null, null];
        }

        $email = $this->darwinbox->verifiedEmail($payload['email'], $payload['token']);

        if (! $email) {
            Log::warning('SSO token rejected by Darwinbox checkToken', ['path' => $request->path()]);

            return [null, null];
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            Log::warning('SSO email has no matching user', ['email' => $email]);
        }

        return [$user, $payload['token']];
    }

    private function handOff(Request $request, string $vendor): Response
    {
        $config = config("services.sso_vendors.$vendor");
        [$user] = $this->verify($request);
        $employee = $user ? Employee::where('employee_id', $user->employee_id)->first() : null;

        if (! $employee) {
            return $this->failed();
        }

        if (empty($config['secret'])) {
            Log::error("SSO vendor '$vendor' has no signing secret configured");

            return $this->failed();
        }

        $jwt = $this->signJwt([
            'iss' => 'KPN',
            'aud' => $config['audience'],
            'iat' => time(),
            'exp' => time() + config('services.sso_vendor_ttl'),
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'business_unit' => $employee->group_company,
            'division' => $employee->unit,
            'location' => $employee->office_area,
            'name' => $employee->fullname,
        ], $config['secret']);

        return redirect()->away($config['url'].'?token='.urlencode($jwt));
    }

    /**
     * HS256 JWT, kept dependency-free because the vendors only need the
     * standard compact form.
     */
    private function signJwt(array $payload, string $secret): string
    {
        $encode = fn (string $data) => rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

        $header = $encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = $encode(json_encode($payload));
        $signature = $encode(hash_hmac('sha256', "$header.$body", $secret, true));

        return "$header.$body.$signature";
    }

    /**
     * A page of its own rather than redirect()->back(): url()->previous() falls
     * back to "/" when there is no Referer, and "/" is the employee SPA, so a
     * failed admin login used to land on its "Mobile Only" screen.
     */
    private function failed(): Response
    {
        return response()->view('errors.sso-failed', [], 403);
    }
}
