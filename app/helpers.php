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

        // Contenu à adresse traduite : chaque langue a son propre slug (voir Controller::findLocalized).
        $parameters = request()->attributes->get('localized_parameters')[$locale] ?? $route->parameters();
        $url = lroute($name, $parameters, $locale);
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

if (! function_exists('meta_description')) {
    /**
     * Description pour Google (150 à 160 caractères conseillés) : un texte trop
     * court est complété par une phrase de contexte, un texte trop long est
     * coupé entre deux mots.
     */
    function meta_description(?string $text, string $complement = ''): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        if ($complement !== '' && mb_strlen($text) < 110) {
            $text = $text === '' ? $complement : rtrim($text, ' .').'. '.$complement;
        }

        return \Illuminate\Support\Str::limit($text, 157, '…', preserveWords: true);
    }
}

if (! function_exists('json_ld')) {
    /**
     * Données structurées Schema.org prêtes à placer dans une balise JSON-LD.
     * La clé « @context » est ajoutée ici : écrite dans une vue, Blade la prend
     * pour sa directive @context et casse le JSON.
     */
    function json_ld(array $data): string
    {
        return json_encode(
            ['@'.'context' => 'https://schema.org'] + $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
        );
    }
}
