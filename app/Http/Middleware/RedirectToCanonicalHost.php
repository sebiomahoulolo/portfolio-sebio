<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirige mahoulolo-sebio.com vers www.mahoulolo-sebio.com (selon APP_URL) :
 * sans cela, Google voit deux copies identiques du site.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($canonicalHost && str_starts_with($canonicalHost, 'www.')
            && $request->getHost() === substr($canonicalHost, 4)) {
            return redirect()->to(
                $request->getScheme().'://'.$canonicalHost.$request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }
}
