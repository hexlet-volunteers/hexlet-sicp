<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Redirect plaintext production requests to their HTTPS counterpart.
     *
     * The original scheme is exposed by Heroku's TLS-terminating router via
     * `X-Forwarded-Proto`, which `TrustProxies` maps onto
     * `$request->isSecure()` — hence this middleware must run after it.
     *
     * The redirect uses 308 rather than 301 so that the method and body of
     * non-GET requests survive it, and so browsers do not cache it forever.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && !$request->isSecure()) {
            return redirect()->secure($request->getRequestUri(), 308);
        }

        return $next($request);
    }
}
