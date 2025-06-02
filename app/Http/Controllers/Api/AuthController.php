<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    // Endpoint untuk login yang diarahkan ke auth-service
    public function login(Request $request)
    {
        try {
            $response = $this->client->get(env('AUTH_SERVICE_URL') . '/auth-service', [
                'json' => $request->all()
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['token'])) {
                $token = $data['token'];
                return redirect()->away(env('APP_URL') . "/login-success?token=" . urlencode($token));
            }

            return response()->json([
                'error' => 'Token not found'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Auth Service unavailable'
            ], 500);
        }
    }
}
