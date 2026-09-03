<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureDevelopmentMode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Local stand-in for the Darwinbox SSO handshake (see SsoController).
 *
 * Only reachable while DEVELOPMENT_MODE=keydevelopment is present in .env;
 * without it GET /admin/login keeps its production behaviour of bouncing the
 * browser back to Darwinbox, and the POST route 404s.
 */
class DevLoginController extends Controller
{
    /**
     * Show the local login form, or fall back to the SSO redirect.
     */
    public function create()
    {
        if (! EnsureDevelopmentMode::enabled()) {
            return redirect()->away('https://kpncorporation.darwinbox.com');
        }

        return view('auth.dev-login');
    }

    /**
     * Log the developer in as an existing user, mirroring what SSO sets up.
     */
    public function store(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['nullable', 'string'],
        ]);

        $identifier = trim($request->input('identifier'));

        $user = User::where('email', $identifier)
            ->orWhere('employee_id', $identifier)
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['identifier' => 'No user found with that Employee ID / email.']);
        }

        // A password is optional here: most kpncorp users only ever sign in
        // through SSO and have no usable hash. When one is typed it still has
        // to match, so an account with a real password can be tested properly.
        if ($request->filled('password')) {
            if (! $user->password || ! Hash::check($request->input('password'), $user->password)) {
                return back()
                    ->withInput($request->except('password'))
                    ->withErrors(['password' => 'Password is incorrect.']);
            }
        }

        Auth::login($user, $request->boolean('remember'));

        // SsoController::handleDbauth() puts the same value on the session.
        $request->session()->put('system', 'kpnem');
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
