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
        // The route parameter cannot contain a slash, so a nested path never
        // reaches here -- but the value is still request input being spliced
        // into a filesystem path, so reduce it to a plain filename first. That
        // also turns a stray ".." into a 404 instead of handing a directory to
        // response()->file().
        $filename = basename($filename);

        $path = storage_path('app/public/'.$filename);

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
