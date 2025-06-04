<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewsController extends Controller
{
    protected $today;

    public function __construct()
    {
        $this->today = Carbon::today();
    }

    public function getNews()
    {
        try {
            // Log untuk memeriksa token dan employee_id
            $payload = JWTAuth::parseToken()->getPayload();
            $employee_id = $payload->get('employee_id');
            Log::info('Token payload employee_id: ' . $employee_id);

            $news = News::all();

            if (!$news) {
                return response()->json(['error' => 'News not found'], 404);
            }

            return response()->json($news);
        } catch (\Exception $e) {
            Log::error('Error getting news: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    public function getNewsDetails($id)
    {
        try {
            // Log untuk memeriksa token dan employee_id
            $payload = JWTAuth::parseToken()->getPayload();
            $employee_id = $payload->get('employee_id');
            $newsId = Crypt::decryptString($id);
            Log::info('Token payload employee_id: ' . $employee_id);

            $news = News::findOrFail($newsId);

            // Refresh agar data terbaru termasuk views baru ikut dikirim
            $news->refresh();

            return response()->json($news);
        } catch (\Exception $e) {
            Log::error('Error getting news: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleLike($id)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $newsId = Crypt::decryptString($id); // jika id terenkripsi
            $news = News::findOrFail($newsId);
    
            // Pastikan kolom like default 0
            if (is_null($news->like)) {
                $news->like = 0;
            }
    
            $news->increment('like');
    
            return response()->json(['success' => true, 'likes' => $news->like]);
        } catch (\Exception $e) {
            Log::error('Error incrementing like: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

}
