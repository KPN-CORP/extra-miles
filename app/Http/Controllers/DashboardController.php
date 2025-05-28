<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\Employee;
=======
>>>>>>> 6ad6b0c67ed9c25b2bfe98e8b37687c0300fc0ab
use Illuminate\Http\Request;

class DashboardController extends Controller
{
<<<<<<< HEAD
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
=======
    public function index()
    {

        $parentLink = 'Dashboard';
        $link = 'Dashboard';

        return view('pages.admin.dashboard.index', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
>>>>>>> 6ad6b0c67ed9c25b2bfe98e8b37687c0300fc0ab
    }
}
