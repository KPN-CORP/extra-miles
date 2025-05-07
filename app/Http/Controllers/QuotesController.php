<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuotesController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Quotes & Affirmation';

        return view('pages.quotes.index', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }
}
