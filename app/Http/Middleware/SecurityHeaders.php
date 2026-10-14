<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité ajoutés à toutes les réponses.
 *
 * La CSP reste volontairement ciblée (cadres, formulaires, plugins) : une
 * politique stricte sur les scripts casserait Livewire/Alpine, qui évaluent
 * leurs expressions à la volée.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
        $headers->remove('X-Powered-By');

        if ($request->isSecure() && app()->isProduction()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // L'administration ne doit jamais rester dans le cache du navigateur ou d'un proxy.
        if ($request->is('admin', 'admin/*', 'livewire/*')) {
            $headers->set('Cache-Control', 'no-store, private');
            $headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
