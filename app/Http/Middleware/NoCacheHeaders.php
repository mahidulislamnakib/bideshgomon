<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheHeaders
{
    /**
     * Handle an incoming request.
     * Prevents browser caching of dynamic API and page responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't cache Inertia responses (dynamic pages)
        if ($request->header('X-Inertia')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        // Don't cache API responses
        if ($request->is('api/*')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        // Allow caching for static assets
        if ($request->is('build/*') || $request->is('storage/*') || $request->is('images/*')) {
            // Cache static assets for 1 year with immutable flag
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        return $response;
    }
}
