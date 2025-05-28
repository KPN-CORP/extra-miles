<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckLocation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Cek apakah user memiliki peran Admin dan ingin melihat report employee
        if ($user->hasRole('Admin') && $request->is('employees*')) {
            // Filter lokasi hanya untuk Jakarta
            $request->merge(['location' => 'Jakarta']);
        }

        return $next($request);
    }
}
