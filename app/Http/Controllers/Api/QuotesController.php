<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Quotes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class QuotesController extends Controller
{
    protected $today;

    public function __construct()
    {
        $this->today = Carbon::today();
    }

    public function getQuotes()
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();

            $datas = Quotes::first();
            
            return response()->json($datas);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Quotes not found'], 400);
        }
    }
}
