<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('lroute')) {
    /**
     * URL d'une route publique dans la langue courante (ou celle demandée).
     * La langue par défaut n'a pas de préfixe ; les autres sont sous /{code}.
     */
    function lroute(string $name, mixed $parameters = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $routeName = $locale === config('portfolio.default_locale') ? $name : "{$locale}.{$name}";

        return route($routeName, $parameters);
    }
}

if (! function_exists('alternate_url')) {
    /**
     * URL de la page courante dans une autre langue (sélecteur de langue, hreflang).
     */
    function alternate_url(string $locale): string
    {
        $route = Route::current();
        $name = $route?->getName();

        if (! $name) {
            return lroute('home', [], $locale);
        }

        foreach (array_keys(config('portfolio.locales')) as $code) {
            $name = preg_replace('/^'.preg_quote($code, '/').'\./', '', $name);
        }

        if (! Route::has($locale === config('portfolio.default_locale') ? $name : "{$locale}.{$name}")) {
            return lroute('home', [], $locale);
        }

        $url = lroute($name, $route->parameters(), $locale);
        $query = request()->getQueryString();

        return $query ? "{$url}?{$query}" : $url;
    }
}

if (! function_exists('media_url')) {
    /**
     * Adresse publique d'un fichier téléversé (images, CV...). Elle suit l'adresse
     * réellement utilisée (domaine, www, https, port) plutôt qu'APP_URL : un écart
     * entre les deux ne casse plus aucune image.
     */
    function media_url(?string $path): ?string
    {
        return filled($path) ? asset('storage/'.ltrim($path, '/')) : null;
    }
}
