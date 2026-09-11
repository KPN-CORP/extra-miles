<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Streams an uploaded image out of storage/app/public.
 */
class ImageController extends Controller
{
    public function __invoke(string $filename): BinaryFileResponse
    {
        $path = storage_path('app/public/'.$filename);

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
