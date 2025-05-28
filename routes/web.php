<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/{any?}', function () {
    return view('user-app');
})->where('any', '^(?!admin).*$');

Route::prefix('admin')->middleware('auth')->group(function () {
    // Admin route here
});

Route::get('/images/{filename}', function ($filename) {
    $path = storage_path('app/public/assets/images/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});