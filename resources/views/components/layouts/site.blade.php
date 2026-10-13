@props([
    'profile',
    'title' => null,
    'description' => null,
    'image' => null,
    'canonical' => null,
    'indexable' => true,
    'type' => 'website',
])

@php
    $initials = Str::of($profile->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('');
    $siteTitle = $title ?? ($profile->translate('seo_title') ?: $profile->name.' — '.$profile->translate('headline'));
    $metaDescription = Str::limit(strip_tags($description ?? ($profile->translate('seo_description') ?: $profile->translate('intro'))), 160);
    $shareImage = $image ?? ($profile->og_image_path ? $profile->mediaUrl($profile->og_image_path) : asset('images/about/3.jpg'));
    $canonicalUrl = $canonical ?? url()->current();
    $home = request()->routeIs('home');
    $anchor = fn (string $id) => $home ? '#'.$id : route('home').'#'.$id;
@endphp

<!DOCTYPE html>
<html lang="fr" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @unless ($indexable)
        <meta name="robots" content="noindex, follow">
    @endunless
    <meta name="theme-color" content="#111111">

    <meta property="og:type" content="{{ $type }}">
    <meta property="og:site_name" content="{{ $profile->name }}">
    <meta property="og:title" content="{{ $siteTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    {{ $head ?? '' }}
</head>
<body>
    <a href="#main" class="skip-link">Aller au contenu</a>

    <header class="site-header">
        <div class="nav-shell">
            <a href="{{ route('home') }}" class="brand" aria-label="{{ $profile->name }} — accueil">
                <span class="brand-mark" aria-hidden="true">{{ $initials }}</span>
                <span>{{ $profile->name }}</span>
            </a>
            <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="Menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <nav id="site-nav" class="nav" aria-label="Navigation principale">
                <a href="{{ $anchor('a-propos') }}">À propos</a>
                <a href="{{ $anchor('competences') }}">Compétences</a>
                <a href="{{ route('projects.index') }}" @class(['is-active' => request()->routeIs('projects.*')])>Projets</a>
                <a href="{{ $anchor('parcours') }}">Parcours</a>
                <a href="{{ $anchor('contact') }}" class="btn btn-primary btn-sm">Me contacter</a>
            </nav>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <span>© <span data-year>{{ date('Y') }}</span> {{ $profile->name }} · {{ $profile->location }}</span>
                <nav class="footer-nav" aria-label="Pied de page">
                    <a href="{{ $anchor('a-propos') }}">À propos</a>
                    <a href="{{ route('projects.index') }}">Projets</a>
                    <a href="{{ $anchor('parcours') }}">Parcours</a>
                    <a href="{{ $anchor('contact') }}">Contact</a>
                    <a href="{{ route('cv') }}" target="_blank" rel="noopener">CV</a>
                </nav>
                <x-site.socials :profile="$profile" />
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
