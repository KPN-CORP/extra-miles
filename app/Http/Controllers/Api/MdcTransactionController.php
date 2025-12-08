<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MdcTransaction;
use Illuminate\Http\Request;

class MdcTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = MdcTransaction::query();

        // 🔍 Filter status (default: Done & Pending)
        if ($request->filled('status')) {
            $status = explode(',', $request->status); // bisa ?status=Done,Pending
            $query->whereIn('status', $status);
        } else {
            $query->whereIn('status', ['Done', 'Pending']);
        }

        // 🔍 Filter created_at between
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $data = $query->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => true,
            'count' => $data->count(),
            'data'  => $data
        ]);
    }
}
