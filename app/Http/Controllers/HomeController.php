<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    function index() {
        return redirect('home');
    }
    function home() {
        $link = 'home';
        // $data = Employee::orderBy("name")->get();
        // $data = Employee::orderBy('name')->paginate(10);
        // $data = Employee::withTrashed()->orderBy('name')->paginate(10); // mengambil semua data berikut yg di delete
        // $data = Employee::onlyTrashed()->orderBy('name')->paginate(10); // hanya mengambil yg di delete
        return view('pages.home', [
            'link' => $link
        ]);
    }

    function starter() {
        $link = 'starter';

        return view('pages.starter', [
            'link' => $link
        ]);
    }

    public function secondLevel(Request $request, $first, $second)
    {
        if ($first == "assets")
            return redirect('home');


        // Cek apakah view tersebut ada sebelum merendernya
        if (view()->exists($first . '.' . $second)) {
            return view($first . '.' . $second);
        } else {
            // Jika view tidak ditemukan, kembalikan ke halaman 404 atau error lain
            return response()->view('errors.404', [], 404);
        }
    }

    public function thirdLevel(Request $request, $first, $second, $third)
    {
        if ($first == "assets")
            return redirect('home');


    return view($first .'.'. $second .'.'. $third);
    }

    public function root(Request $request, $first)
    {

        $mode = $request->query('mode');
        $demo = $request->query('demo');
     
        if ($first == "assets")
            return redirect('home');

        return view($first);
    }

}