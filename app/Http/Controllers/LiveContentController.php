<?php

namespace App\Http\Controllers;

use App\Models\LiveContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiveContentController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Live Content';
        
        $liveContents = LiveContent::latest()->get(); 

        $liveArchive = LiveContent::onlyTrashed()
        ->orderBy('created_at', 'desc')
        ->get();

        return view('pages.admin.live.index', [
            'link' => $link,
            'parentLink' => $parentLink,
            'liveContents' => $liveContents,
            'liveArchive' => $liveArchive,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content_link' => 'required|string',
        ]);

        LiveContent::query()->delete();

        LiveContent::create([
            'title' => $request->title,
            'content_link' => $request->content_link,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->route('live.index')->with('success', 'Live content created successfully.');
    }

    public function destroy($id)
    {
        $live = LiveContent::findOrFail($id);
        $live->delete(); // Soft delete
        return redirect()->route('live.index')->with('success', 'Live content archived successfully.');
    }
}
