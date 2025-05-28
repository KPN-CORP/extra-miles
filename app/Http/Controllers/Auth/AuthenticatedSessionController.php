<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (Auth::check()) {
            Session::flash("toast", [
                "type" => "success",
                "message" => "You're logged in."
            ]);
            return redirect()->intended(url('/'));
        } else {
            Alert::error('Error', 'Email or password is incorrect.')->showConfirmButton('OK');
            return redirect()->back();
        }

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        // Memastikan bahwa $user adalah instance dari model User
        if($user instanceof \App\Models\User) {
            $user->token = null; 
            $user->save();
        } else {
            // Jika $user bukan instance dari model User
            // Lakukan penanganan kesalahan sesuai kebutuhan Anda
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
