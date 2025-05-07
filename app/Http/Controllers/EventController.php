<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Event Management';

        return view('pages.events.index', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }

    public function create()
    {
        $parentLink = 'Event Management';
        $link = 'Create';

        return view('pages.events.create', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }
}
