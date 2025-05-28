<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\LiveContent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class LiveContentController extends Controller
{
    protected $today;

    public function __construct()
    {
        $this->today = Carbon::today();
    }

    public function getLiveContent()
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();

            $datas = LiveContent::first();

            return response()->json($datas);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Off Air'], 400);
        }
    }
}
