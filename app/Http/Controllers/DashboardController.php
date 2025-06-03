<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {

        $parentLink = 'Dashboard';
        $link = 'Dashboard';

        return view('pages.admin.dashboard.index', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }
}
