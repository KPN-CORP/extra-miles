<?php

namespace App\Http\Controllers;

use App\Models\social;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Social Media';

        $listSocial = social::whereNull('deleted_at')->get();

        $socialArchive = social::onlyTrashed()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.admin.social.index', [
            'link' => $link,
            'parentLink' => $parentLink,
            'listSocial' => $listSocial,
            'socialArchive' => $socialArchive,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required',
            'businessunit' => 'required',
            'link' => 'required|string',
        ]);

        social::create([
            'category' => $request->category,
            'businessUnit' => $request->businessunit,
            'link' => $request->link,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', __('Social successfully created!'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category' => 'required',
            'businessunit' => 'required',
            'link' => 'required|string',
        ]);

        $social = social::findOrFail($id);
        $social->update([
            'category' => $request->category,
            'businessUnit' => $request->businessunit,
            'link' => $request->link,
        ]);

        return redirect()->back()->with('success', __('Social updated successfully.'));
    }

    public function destroy($id)
    {
        $quote = social::findOrFail($id);
        $quote->delete();

        return redirect()->back()->with('success', __('Social archived successfully.'));
    }
}
