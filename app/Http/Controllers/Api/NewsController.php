<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsLike;
use App\Models\NewsView;
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

    public function getNews(Request $request)
    {
        try {
            JWTAuth::parseToken()->getPayload();

            // Listing views only render these columns. Selecting explicitly keeps
            // the long `content` HTML out of the response -- the detail endpoint
            // serves that per article.
            $query = News::select(
                'id', 'category', 'title', 'publish_date', 'image', 'businessUnit', 'created_at'
            )->orderBy('publish_date', 'desc');

            // The dashboard only shows the newest few, so it can ask for a slice
            // instead of pulling the whole table down and discarding it.
            $limit = (int) $request->query('limit', 0);
            if ($limit > 0) {
                $query->limit($limit);
            }

            return response()->json($query->get());
        } catch (\Exception $e) {
            Log::error('Error getting news: '.$e->getMessage());

            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    public function getNewsDetails($id, Request $request)
    {
        try {
            // Log untuk memeriksa token dan employee_id
            $payload = JWTAuth::parseToken()->getPayload();
            $employee_id = $payload->get('employee_id');
            $id = Crypt::decryptString($id);
            Log::info('Token payload employee_id: '.$employee_id);

            $news = News::with('newsLikes')->findOrFail($id);

            // Refresh agar data terbaru termasuk views baru ikut dikirim
            $news->refresh();

            return response()->json($news);
        } catch (\Exception $e) {
            Log::error('Error getting news: '.$e->getMessage());

            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    public function recordView(Request $request, $id)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $employee_id = $payload->get('employee_id');
            $id = Crypt::decryptString($id);
            $news = News::findOrFail($id);

            NewsView::create([
                'news_id' => $news->id,
                'employee_id' => $employee_id, // bisa null
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Opsional: tambahkan hitungan view
            $news->increment('view_count');

            return response()->json(['message' => 'View recorded.']);
        } catch (\Exception $e) {
            Log::error('Error record news: '.$e->getMessage());

            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    // ✅ Like artikel
    public function like($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $payload = JWTAuth::parseToken()->getPayload();
            $employee_id = $payload->get('employee_id');
            $news = News::findOrFail($id);

            // ✅ Cek apakah like sudah pernah dilakukan
            $alreadyLiked = NewsLike::where('news_id', $news->id)
                ->where('employee_id', $employee_id)
                ->exists();

            if ($alreadyLiked) {
                return response()->json(['message' => 'Already liked'], 200);
            }

            // ✅ Simpan like
            NewsLike::create([
                'news_id' => $news->id,
                'employee_id' => $employee_id,
            ]);

            return response()->json(['message' => 'News liked'], 201);
        } catch (\Exception $e) {
            Log::error('Error record like: '.$e->getMessage());

            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Unlike artikel
    public function unlike($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $payload = JWTAuth::parseToken()->getPayload();
            $employee_id = $payload->get('employee_id');
            NewsLike::where('news_id', $id)->where('employee_id', $employee_id)->delete();

            return response()->json(['message' => 'Like removed']);
        } catch (\Exception $e) {
            Log::error('Error record unlike: '.$e->getMessage());

            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }
}
