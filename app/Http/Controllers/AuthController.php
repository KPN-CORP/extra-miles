<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    function login() {
        return view('pages.auth.login');
    }
    function auth() {
        return redirect('login');
    }
    function logout() {
        return redirect('login');
    }
}
