<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Local stand-in for the Darwinbox -> auth-service handshake that hands the
 * employee SPA its JWT (see Api\AuthController).
 *
 * Production mints the token on an external auth-service and bounces the
 * browser to APP_URL/login-success?token=..., where ConfirmLogin.jsx verifies
 * it against /api/verify and drops it into sessionStorage. This controller
 * mints the same shape of token locally and redirects to the same place, so
 * the SPA is reached through its real entry point without any SSO round trip.
 *
 * Only reachable while DEVELOPMENT_MODE=keydevelopment is present in .env;
 * without it both routes 404 exactly as if they were never registered.
 */
class DevMobileLoginController extends Controller
{
    /**
     * Show the local token form.
     */
    public function create()
    {
        return view('auth.dev-mobile-login');
    }

    /**
     * Mint a JWT for an existing employee and hand it to the SPA.
     */
    public function store(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'show_token' => ['nullable', 'boolean'],
        ]);

        $identifier = trim($request->input('identifier'));

        $user = User::where('email', $identifier)
            ->orWhere('employee_id', $identifier)
            ->first();

        if (! $user) {
            return $this->fail($request, 'identifier', 'No user found with that Employee ID / email.');
        }

        // Every mobile endpoint reads the employee_id claim off the payload
        // rather than the authenticated user, so a token without it logs in
        // fine and then 404s on /api/profile. Fail here instead, where the
        // reason is visible.
        if (! $user->employee_id) {
            return $this->fail($request, 'identifier', "User \"{$user->name}\" has no employee_id, which the mobile API needs on every request.");
        }

        $employee = Employee::where('employee_id', $user->employee_id)->first();

        if (! $employee) {
            return $this->fail($request, 'identifier', "No employee record for employee_id \"{$user->employee_id}\" on the kpncorp connection.");
        }

        $token = $this->issueToken($user, $employee);

        if ($request->boolean('show_token')) {
            return back()
                ->withInput($request->except('show_token'))
                ->with('devToken', $token);
        }

        // Same landing route the real auth-service redirects to.
        return redirect()->to('/login-success?token='.urlencode($token));
    }

    /**
     * Build a token carrying the claims the API actually consumes.
     *
     * App\Models\User does not implement JWTSubject, so JWTAuth::fromUser()
     * is unavailable; the payload is assembled claim by claim instead. Only
     * sub, employee_id and fullname are read anywhere in the app. TTL is the
     * package default -- set JWT_TTL in .env to keep a dev session alive
     * longer.
     */
    protected function issueToken(User $user, Employee $employee): string
    {
        $payload = JWTAuth::factory()->customClaims([
            'sub' => (string) $user->id,
            'employee_id' => $employee->employee_id,
            'fullname' => $employee->fullname,
        ])->make();

        return JWTAuth::encode($payload)->get();
    }

    /**
     * Bounce back to the form with an error against the given field.
     */
    protected function fail(Request $request, string $field, string $message)
    {
        return back()
            ->withInput($request->all())
            ->withErrors([$field => $message]);
    }
}
