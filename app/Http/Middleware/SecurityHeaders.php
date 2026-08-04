<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds transport and feature-policy headers to every web response.
 *
 * nginx already sets X-Frame-Options, X-Content-Type-Options and
 * Referrer-Policy; these are the ones it does not, kept in the application so
 * they survive a control-panel rewrite of the vhost config.
 *
 * Deliberately NOT set here:
 *
 *  - Content-Security-Policy. The layouts use inline <script> and <style>
 *    throughout and load Google Fonts, Font Awesome and Quill from CDNs, so
 *    any useful policy would break the site until those are refactored.
 *  - includeSubDomains on HSTS. It would force HTTPS on every current and
 *    future subdomain of neralgov.com, which is not safe to assume remotely.
 */
class SecurityHeaders
{
    /** One year, the minimum for HSTS preload eligibility. */
    private const HSTS_MAX_AGE = 31536000;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HSTS is only meaningful - and only honoured - over a secure connection.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=' . self::HSTS_MAX_AGE
            );
        }

        // Browser features this portal never uses. "payment" is intentionally
        // left unrestricted so gateway checkout flows are not affected.
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        return $response;
    }
}
