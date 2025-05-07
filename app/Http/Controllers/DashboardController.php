<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {

        $parentLink = 'Dashboard';
        $link = 'Dashboard';

        return view('dashboard.index', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }
}
