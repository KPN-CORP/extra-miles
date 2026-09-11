<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * Locales the admin back-office ships translations for.
     *
     * Kept in one place so the middleware, the topbar switcher and this
     * controller cannot drift apart.
     */
    public const SUPPORTED = ['en', 'id'];

    public function switchLanguage(Request $request, string $locale)
    {
        // An unknown locale would otherwise be stored in the session and make
        // every later request fall back silently, so reject it up front.
        abort_unless(in_array($locale, self::SUPPORTED, true), 404);

        App::setLocale($locale);
        session(['locale' => $locale]);

        return back();
    }
}
