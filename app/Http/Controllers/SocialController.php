<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Social Media';

        return view('pages.admin.social.index', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }
}
