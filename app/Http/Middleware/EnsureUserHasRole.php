<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;
use RealRashid\SweetAlert\Facades\Alert;

// use Auth;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Dapatkan pengguna saat ini
        $user = $request->user();
        // return $next($request);

        // Periksa apakah pengguna memiliki peran yang sesuai
        if ($user && ($user->hasRole('admin') || $user->hasRole('superadmin'))) {
            // Jika pengguna memiliki peran yang sesuai, lanjutkan ke tindakan selanjutnya
            return $next($request);
        } else {
            // Jika pengguna tidak memiliki peran yang sesuai, arahkan pengguna kembali dengan pesan kesalahan
            //return Redirect::to('/home')->withErrors('You do not have permission to access this page.');
            Alert::error('You do not have permission to access this page.')->showConfirmButton('OK');
            return redirect()->intended(url('/'));
        }
    }
}
