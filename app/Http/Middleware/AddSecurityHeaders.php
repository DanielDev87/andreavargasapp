<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser-side hardening sent with every page.
 */
class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // No other site may show these pages inside a frame (clickjacking)
            'X-Frame-Options' => 'SAMEORIGIN',
            // Browsers must trust the declared content type, not guess it from the bytes
            'X-Content-Type-Options' => 'nosniff',
            // Other sites only learn our domain, never full paths; Bunny still sees the
            // domain it needs for its Allowed domains check
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // The camera is only for the admin's attendance QR scanner
            'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=(), payment=()',
        ];

        // Once a browser has seen the site over HTTPS, it refuses plain HTTP for a year
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
