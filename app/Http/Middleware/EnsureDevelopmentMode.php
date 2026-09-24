<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates routes that must only exist on a developer machine.
 *
 * The route is hidden with a 404 (rather than a 403) so that an environment
 * without DEVELOPMENT_MODE set behaves exactly as if the route was never
 * registered at all.
 */
class EnsureDevelopmentMode
{
    /**
     * Whether DEVELOPMENT_MODE in .env holds the expected key. The key is in
     * source, so it is only a switch -- the local-environment check is what
     * keeps these SSO-bypassing logins off a server.
     */
    public static function enabled(): bool
    {
        $key = config('app.development_mode_key');

        return app()->environment('local')
            && is_string($key) && $key !== '' && config('app.development_mode') === $key;
    }

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(static::enabled(), 404);

        return $next($request);
    }
}
