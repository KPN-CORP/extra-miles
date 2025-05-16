<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Survey/Voting';

        return view('pages.admin.survey.index', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }
}
