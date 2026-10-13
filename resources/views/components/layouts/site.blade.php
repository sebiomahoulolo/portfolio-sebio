@props([
    'profile',
    'title' => null,
    'description' => null,
    'image' => null,
    'indexable' => true,
    'type' => 'website',
])

@php
    $locale = app()->getLocale();
    $locales = config('portfolio.locales');
    $initials = Str::of($profile->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('');
    $siteTitle = $title ?? ($profile->translate('seo_title') ?: $profile->name.' — '.$profile->translate('headline'));
    $metaDescription = Str::limit(strip_tags($description ?? ($profile->translate('seo_description') ?: $profile->translate('intro'))), 160);
    $shareImage = $image ?? ($profile->og_image_path ? $profile->mediaUrl($profile->og_image_path) : asset('images/about/3.jpg'));
    $canonicalUrl = alternate_url($locale);
    $home = request()->routeIs('home', 'en.home');
    $anchor = fn (string $id) => $home ? '#'.$id : lroute('home').'#'.$id;
    $is = fn (string $name) => request()->routeIs($name, 'en.'.$name);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Thème appliqué avant l'affichage pour éviter un flash de couleur. --}}
    <script>
        (function () {
            var theme;
            try { theme = localStorage.getItem('theme'); } catch (e) {}
            if (theme !== 'dark' && theme !== 'light') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <title>{{ $siteTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @foreach ($locales as $code => $name)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ alternate_url($code) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ alternate_url(config('portfolio.default_locale')) }}">
    @unless ($indexable)
        <meta name="robots" content="noindex, follow">
    @endunless
    <meta name="theme-color" content="#F5F3EF">

    <meta property="og:type" content="{{ $type }}">
    <meta property="og:site_name" content="{{ $profile->name }}">
    <meta property="og:title" content="{{ $siteTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:locale" content="{{ $locale === 'fr' ? 'fr_FR' : 'en_US' }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    {{ $head ?? '' }}
</head>
<body>
    <a href="#main" class="skip-link">{{ __('Aller au contenu') }}</a>
    <div class="scroll-progress" aria-hidden="true"></div>

    <header class="site-header">
        <div class="nav-shell">
            <a href="{{ lroute('home') }}" class="brand" aria-label="{{ $profile->name }} — {{ __('accueil') }}">
                <span class="brand-mark" aria-hidden="true">{{ $initials }}</span>
                <span class="brand-name">{{ $profile->name }}</span>
            </a>

            <nav id="site-nav" class="nav" aria-label="{{ __('Navigation principale') }}">
                <a href="{{ $anchor('a-propos') }}">{{ __('À propos') }}</a>
                <a href="{{ lroute('services.index') }}" @class(['is-active' => $is('services.*')])>{{ __('Services') }}</a>
                <a href="{{ lroute('projects.index') }}" @class(['is-active' => $is('projects.*')])>{{ __('Projets') }}</a>
                <a href="{{ $anchor('parcours') }}">{{ __('Parcours') }}</a>
                <a href="{{ $anchor('contact') }}" class="btn btn-primary btn-sm" data-magnetic>{{ __('Me contacter') }}</a>
            </nav>

            <div class="nav-tools">
                <div class="lang-switch" data-current="{{ array_search($locale, array_keys($locales)) }}" role="group" aria-label="{{ __('Langue') }}">
                    @foreach ($locales as $code => $name)
                        <a href="{{ alternate_url($code) }}" hreflang="{{ $code }}" lang="{{ $code }}" title="{{ $name }}"
                            @class(['is-current' => $code === $locale]) @if ($code === $locale) aria-current="true" @endif>{{ strtoupper($code) }}</a>
                    @endforeach
                </div>
                <button type="button" class="theme-toggle" data-theme-toggle aria-label="{{ __('Changer de thème') }}" title="{{ __('Mode clair / sombre') }}">
                    <i class="fas fa-sun" aria-hidden="true"></i>
                    <i class="fas fa-moon" aria-hidden="true"></i>
                </button>
                <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="{{ __('Menu') }}">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <span>© <span data-year>{{ date('Y') }}</span> {{ $profile->name }} · {{ $profile->location }}</span>
                <nav class="footer-nav" aria-label="{{ __('Pied de page') }}">
                    <a href="{{ lroute('services.index') }}">{{ __('Services') }}</a>
                    <a href="{{ lroute('projects.index') }}">{{ __('Projets') }}</a>
                    <a href="{{ $anchor('parcours') }}">{{ __('Parcours') }}</a>
                    <a href="{{ $anchor('contact') }}">{{ __('Contact') }}</a>
                    <a href="{{ route('cv') }}" target="_blank" rel="noopener">{{ __('CV') }}</a>
                </nav>
                <x-site.socials :profile="$profile" />
            </div>
        </div>
    </footer>

    <button type="button" class="back-to-top" aria-label="{{ __('Revenir en haut') }}"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>

    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
