<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Services\DarwinboxClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

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
        $email = $payload ? $this->darwinbox->verifiedEmail($payload['email'], $payload['token']) : null;

        if (! $email) {
            return [null, null];
        }

        return [User::where('email', $email)->first(), $payload['token']];
    }

    private function handOff(Request $request, string $vendor): RedirectResponse
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

    private function failed(): RedirectResponse
    {
        Alert::error(__('Login Failed, Please Contact Administrator'))->showConfirmButton(__('OK'));

        return url()->previous()
            ? redirect()->back()
            : redirect()->away('https://kpncorporation.darwinbox.com/');
    }
}
