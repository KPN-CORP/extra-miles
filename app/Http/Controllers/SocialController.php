<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\social;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Social Media';

        $listSocial = Social::whereNull('deleted_at')->get();

        $socialArchive = Social::onlyTrashed()
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

        Social::create([
            'category'      => $request->category,
            'businessUnit'  => $request->businessunit,
            'link'          => $request->link,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Social successfully created!');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'category' => 'required',
            'businessunit' => 'required',
            'link' => 'required|string',
        ]);

        $social = Social::findOrFail($id);
        $social->update([
            'category' => $request->category,
            'businessUnit' => $request->businessunit,
            'link' => $request->link,
        ]);

        return redirect()->back()->with('success', 'Social updated successfully.');
    }
    public function destroy($id)
    {
        $quote = Social::findOrFail($id);
        $quote->delete();

        return redirect()->back()->with('success', 'Social archived successfully.');
    }
}
