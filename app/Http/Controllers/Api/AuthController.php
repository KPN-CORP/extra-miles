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
            'timeout' => 15, // agar tidak menggantung terlalu lama
        ]);
    }

    // Endpoint untuk login yang diarahkan ke auth-service
    public function login(Request $request)
    {
        $maxRetries = 5;
        $retryDelayMs = 300;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $this->client->get(env('AUTH_SERVICE_URL') . '/auth-service', [
                    'json' => $request->all(),
                ]);

                $data = json_decode($response->getBody(), true);

                if (isset($data['token'])) {
                    return redirect()->away(env('APP_URL') . "/login-success?token=" . urlencode($data['token']));
                }

                return redirect()->away(env('APP_URL') . "/login-failed?error=" . urlencode("Token not found in response"));
            } catch (RequestException $e) {
                $message = $e->hasResponse()
                    ? $e->getResponse()->getBody()->getContents()
                    : $e->getMessage();

                Log::warning("Login attempt $attempt failed: $message");

                if ($attempt < $maxRetries) {
                    usleep($retryDelayMs * 1000); // jeda sebelum retry
                } else {
                    Log::error('Final login error from auth-service after multiple attempts: ' . $message);
                    return redirect()->away(env('APP_URL') . "/login-failed?error=" . urlencode("Service is unavailable. Please try again later."));
                }
            } catch (\Exception $e) {
                Log::error('Unexpected login error: ' . $e->getMessage());
                return redirect()->away(env('APP_URL') . "/login-failed?error=" . urlencode("Unexpected server error occurred."));
            }
        }
    }
}
