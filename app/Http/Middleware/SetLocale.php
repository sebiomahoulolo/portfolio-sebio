<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fixe la langue à partir du groupe de routes (« locale:fr », « locale:en »).
 */
class SetLocale
{
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        if (! array_key_exists($locale, config('portfolio.locales'))) {
            $locale = config('portfolio.default_locale');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
