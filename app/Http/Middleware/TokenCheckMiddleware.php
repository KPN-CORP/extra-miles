<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class TokenCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        $user = Auth::user();

        // Periksa apakah pengguna ada dan memiliki token
        if ($user && $user->token) {
            // Lakukan pengecekan token di sini
            $token = $user->token;

            // Lakukan pengecekan token ke API Darwinbox
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://kpncorporation.darwinbox.com/checkToken',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode(array(
                    "api_key" => "3bbfc6dfa28df2a81bd45192bf4f96b72628ae0ec9921a062aef937b7f25d6c704ccfc9539e70e5939a45cc43f3b7ce61477c7135a83bdbd6f85d5c38b5fc563",
                    "token" => $token,
                )),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Basic S1BOX1NTTzpUTXNfJDU2T3BzJXB3',
                    // Sesuaikan header sesuai kebutuhan
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $responseData = json_decode($response, true);

            // Ambil nilai status
            $tokenIsValid = $responseData['status'];

            // Misalnya, jika token tidak valid, lakukan logout
            if ($tokenIsValid==0) {
                Alert::success('Your session has expired. Please login again.')->showConfirmButton('OK');
                Auth::logout();
                return redirect('/');
                //return redirect('/login')->with('error', 'Your session has expired. Please login again.');

                // $user = Auth::user();
                // // Memastikan bahwa $user adalah instance dari model User
                // if($user instanceof \App\Models\User) {
                //     $user->token = null; 
                //     $user->save();
                // } else {
                //     // Jika $user bukan instance dari model User
                //     // Lakukan penanganan kesalahan sesuai kebutuhan Anda
                // }
                
                // Alert::success('Your session has expired. Please login again.')->showConfirmButton('OK');

                // Auth::guard('web')->logout();
                // $request->session()->invalidate();
                // $request->session()->regenerateToken();

                // return redirect('/');
            }
        }

        return $next($request);
        
    }
}
