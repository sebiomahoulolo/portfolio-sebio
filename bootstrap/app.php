<?php

use App\Http\Middleware\RedirectToCanonicalHost;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hostinger place le site derrière son CDN (hcdn) : on fait confiance
        // aux en-têtes X-Forwarded-* pour détecter correctement HTTPS et l'IP.
        $middleware->trustProxies(at: '*');

        $middleware->append(RedirectToCanonicalHost::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->alias(['locale' => SetLocale::class]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
