<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Attach a baseline set of security headers to every response.
     *
     * `Strict-Transport-Security` is only meaningful over TLS, so it is sent
     * on secure responses alone. Behind Heroku's TLS-terminating router the
     * original scheme reaches the app via `X-Forwarded-Proto`, which
     * `TrustProxies` maps onto `$request->isSecure()`.
     *
     * `X-XSS-Protection` is pinned to `0`: the legacy auditor can itself
     * introduce vulnerabilities, so OWASP recommends disabling it in favour
     * of a Content-Security-Policy.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
            'X-XSS-Protection' => '0',
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
