<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Catches unmatched /admin/* paths and renders the admin 404 page.
 */
class AdminFallbackController extends Controller
{
    public function __invoke(): View
    {
        return view('errors.404');
    }
}
