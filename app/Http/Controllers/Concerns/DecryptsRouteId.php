<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

trait DecryptsRouteId
{
    /**
     * Route params carry Crypt::encryptString() ids, never raw primary keys.
     * A tampered id is a 404, not a 500.
     */
    protected function decryptId(string $encryptedId): int
    {
        try {
            return (int) Crypt::decryptString($encryptedId);
        } catch (DecryptException) {
            abort(404);
        }
    }
}
