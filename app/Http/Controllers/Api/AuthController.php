<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;

class AuthController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 5, // agar tidak menggantung terlalu lama
        ]);
    }

    // Endpoint untuk login yang diarahkan ke auth-service
    public function login(Request $request)
    {
        try {
            $response = $this->client->get(env('AUTH_SERVICE_URL') . '/auth-service', [
                'json' => $request->all(),
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['token'])) {
                $token = $data['token'];
                return redirect()->away(env('APP_URL') . "/login-success?token=" . urlencode($token));
            }

            return response()->json([
                'error' => 'Token not found in response',
            ], 400);
        } catch (RequestException $e) {
            $message = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage();

            Log::error('Login error from auth-service: ' . $message);

            return response()->json([
                'error' => 'Service is unavailable, please try again in few minutes',
            ], 503); // gunakan 503 untuk service unavailable
        } catch (\Exception $e) {
            Log::error('Unexpected login error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Unexpected server error',
            ], 500);
        }
    }
}
