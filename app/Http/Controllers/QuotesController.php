<?php

namespace App\Http\Controllers;

use App\Models\Quotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotesController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Quotes & Affirmation';

        $listQuotes = Quotes::whereNull('deleted_at')->get();

        $quoteArchive = Quotes::onlyTrashed()
        ->orderBy('created_at', 'desc')
        ->get();

        return view('pages.admin.quotes.index', [
            'link' => $link,
            'parentLink' => $parentLink,
            'listQuotes' => $listQuotes,
            'quoteArchive' => $quoteArchive,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'author' => 'required|string|max:255',
            'quote' => 'required|string',
        ]);

        Quotes::create([
            'author'     => $request->author,
            'quotes'     => $request->quote,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Quote successfully created!');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'author' => 'required|string|max:255',
            'quotes' => 'required|string',
        ]);

        $quote = Quotes::findOrFail($id);
        $quote->update([
            'author' => $request->author,
            'quotes' => $request->quotes,
        ]);

        return redirect()->back()->with('success', 'Quote updated successfully.');
    }
    public function destroy($id)
    {
        $quote = Quotes::findOrFail($id);
        $quote->delete();

        return redirect()->back()->with('success', 'Quote archived successfully.');
    }
}
