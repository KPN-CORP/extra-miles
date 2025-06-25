<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Social;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialController extends Controller
{
    protected $today;

    public function __construct()
    {
        $this->today = Carbon::today();
    }

    public function index()
    {
        try {
            // Log untuk memeriksa token dan employee_id
            $payload = JWTAuth::parseToken()->getPayload();
            $employee_id = $payload->get('employee_id');
            Log::info('Token payload employee_id: ' . $employee_id);

            $social = Social::all();

            if (!$social) {
                return response()->json(['error' => 'Social not found'], 404);
            }

            return response()->json($social);
        } catch (\Exception $e) {
            Log::error('Error getting news: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }
}
