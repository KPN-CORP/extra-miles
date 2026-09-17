<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Streams an uploaded image out of storage/app/public.
 *
 * Melayani path bersarang juga (mis. "assets/images/news/news_4.jpg"), karena
 * itu bentuk yang dipakai News/Event/Survey saat menyimpan ke disk 'public'.
 * URL /storage/... tidak bisa dipakai di server: document root cPanel adalah
 * folder terpisah dari public/ aplikasi, jadi symlink storage tidak ada di
 * sana dan gambar lama tampil rusak.
 */
class ImageController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse
    {
        $base = realpath(storage_path('app/public'));

        if ($base === false) {
            abort(404);
        }

        // Nilai ini input request yang disambung ke path filesystem. Buang
        // prefix "storage/" peninggalan URL lama, lalu andalkan realpath +
        // pengecekan prefix di bawah supaya "../" keluar sebagai 404, bukan
        // file di luar storage/app/public.
        $relative = ltrim(str_replace('\\', '/', $path), '/');
        $relative = preg_replace('#^storage/#', '', $relative);

        $full = realpath($base.DIRECTORY_SEPARATOR.$relative);

        if ($full === false
            || ! str_starts_with($full, $base.DIRECTORY_SEPARATOR)
            || ! is_file($full)) {
            abort(404);
        }

        return response()->file($full);
    }
}
