<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Serves the Blade shell that boots the React employee app. Every path that is
 * not under /admin falls through to this so client-side routing works on a
 * cold page load.
 */
class SpaController extends Controller
{
    public function __invoke(): View
    {
        return view('user-app');
    }
}
