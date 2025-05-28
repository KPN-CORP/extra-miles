<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function selfResetView(): View
    {
        return view('auth.reset-password-email');
    }

    public function selfReset(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('reset-password-email');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $userExists = User::where('email', $request->email)->exists();

        if (!$userExists) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'No user found with that email address.']);
        }
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        $messages = [
            'passwords.sent' => 'The password reset link has been sent to your email. Please check your inbox.',
            'passwords.user' => 'No user found with that email address.',
        ];

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', $messages['passwords.sent']);
        } else {
            return back()->withInput($request->only('email'))->withErrors(['email' => $messages['passwords.user']]);
        }
    }
}
