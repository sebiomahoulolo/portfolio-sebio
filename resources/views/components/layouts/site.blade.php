@props([
    'profile',
    'title' => null,
    'description' => null,
    'image' => null,
    'indexable' => true,
    'type' => 'website',
    // Fil d'Ariane pour Google : [libellé => url], l'accueil est ajouté automatiquement.
    'breadcrumbs' => [],
    'published' => null,
])

@php
    $locale = app()->getLocale();
    $locales = config('portfolio.locales');
    $initials = Str::of($profile->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('');
    $siteTitle = $title ?? ($profile->translate('seo_title') ?: $profile->name.' — '.$profile->translate('headline'));
    // Au-delà de 65 caractères, Google coupe le titre : on retire alors le nom en suffixe.
    if (mb_strlen($siteTitle) > 65 && Str::endsWith($siteTitle, ' — '.$profile->name)) {
        $siteTitle = Str::beforeLast($siteTitle, ' — '.$profile->name);
    }
    $metaDescription = meta_description($description ?? ($profile->translate('seo_description') ?: $profile->translate('intro')));
    $shareImage = $image ?? ($profile->og_image_path ? $profile->mediaUrl($profile->og_image_path) : $profile->photoUrl());
    $canonicalUrl = alternate_url($locale);
    $home = request()->routeIs('home', 'en.home');
    $anchor = fn (string $id) => $home ? '#'.$id : lroute('home').'#'.$id;
    $is = fn (string $name) => request()->routeIs($name, 'en.'.$name);
    // Pas de Google Analytics pour l'administrateur connecté : ses visites fausseraient les chiffres.
    $analyticsId = auth()->check() ? null : config('portfolio.seo.google_analytics');
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <meta property="og:image:alt" content="{{ $siteTitle }}">
    <meta property="og:locale" content="{{ $locale === 'fr' ? 'fr_FR' : 'en_US' }}">
    <meta property="og:locale:alternate" content="{{ $locale === 'fr' ? 'en_US' : 'fr_FR' }}">
    @if ($published)
        <meta property="article:published_time" content="{{ $published->toAtomString() }}">
        <meta property="article:author" content="{{ $profile->name }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $siteTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    <meta name="author" content="{{ $profile->name }}">

    @if ($code = config('portfolio.seo.google_verification'))
        <meta name="google-site-verification" content="{{ $code }}">
    @endif
    @if ($code = config('portfolio.seo.bing_verification'))
        <meta name="msvalidate.01" content="{{ $code }}">
    @endif

    @if ($home)
        <script type="application/ld+json">
            {!! json_ld([
                '@type' => 'WebSite',
                'name' => $profile->name,
                'url' => lroute('home'),
                'inLanguage' => $locale,
                'author' => ['@type' => 'Person', 'name' => $profile->name],
            ]) !!}
        </script>
    @elseif ($breadcrumbs)
        <script type="application/ld+json">
            {!! json_ld([
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect([__('Accueil') => lroute('home')] + $breadcrumbs)
                    ->map(fn ($url, $label) => ['name' => $label, 'item' => $url])
                    ->values()
                    ->map(fn ($item, $i) => ['@type' => 'ListItem', 'position' => $i + 1] + $item)
                    ->all(),
            ]) !!}
        </script>
    @endif

    @include('partials.favicons')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    {{ $head ?? '' }}
</head>
<body data-track="{{ route('analytics.store') }}" data-print-notice="{{ __('Ce contenu est protégé et ne peut pas être imprimé. Retrouvez-le en ligne sur :url', ['url' => url('/')]) }}">
    <a href="#main" class="skip-link">{{ __('Aller au contenu') }}</a>

    <header class="site-header">
        <div class="nav-shell">
            <a href="{{ lroute('home') }}" class="brand" aria-label="{{ $profile->name }} — {{ __('accueil') }}">
                <span class="brand-mark" aria-hidden="true">{{ $initials }}</span>
                <span class="brand-name">{{ $profile->name }}</span>
            </a>

            {{-- data-section : section de l'accueil qui active le lien pendant le défilement. --}}
            <nav id="site-nav" class="nav" aria-label="{{ __('Navigation principale') }}">
                <span class="nav-indicator" aria-hidden="true"></span>
                <a href="{{ $anchor('a-propos') }}" data-section="a-propos">{{ __('À propos') }}</a>
                <a href="{{ lroute('services.index') }}" data-section="services" @class(['is-active' => $is('services.*')])>{{ __('Services') }}</a>
                <a href="{{ lroute('projects.index') }}" data-section="projets" @class(['is-active' => $is('projects.*')])>{{ __('Projets') }}</a>
                <a href="{{ lroute('blog.index') }}" data-section="blog" @class(['is-active' => $is('blog.*')])>{{ __('Blog') }}</a>
                <a href="{{ $anchor('parcours') }}" data-section="parcours">{{ __('Parcours') }}</a>
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
                <button class="nav-toggle" type="button" aria-controls="mobile-menu" aria-expanded="false" aria-label="{{ __('Ouvrir le menu') }}">
                    <span class="burger" aria-hidden="true"><span></span><span></span><span></span></span>
                </button>
            </div>
            <span class="nav-progress" aria-hidden="true"></span>
        </div>
    </header>

    {{-- Menu mobile : panneau qui glisse depuis la droite. --}}
    <div class="mobile-menu" id="mobile-menu" role="dialog" aria-modal="true" aria-label="{{ __('Menu') }}" inert>
        <div class="mobile-menu-backdrop" data-menu-close></div>
        <div class="mobile-menu-panel">
            <div class="mobile-menu-head">
                <span class="tile-label"><i class="fas fa-compass" aria-hidden="true"></i> {{ __('Menu') }}</span>
                <button type="button" class="mobile-menu-close" data-menu-close aria-label="{{ __('Fermer le menu') }}">
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            <nav class="mobile-menu-links" aria-label="{{ __('Navigation principale') }}">
                @foreach ([
                    [$anchor('a-propos'), __('À propos'), false],
                    [lroute('services.index'), __('Services'), $is('services.*')],
                    [lroute('projects.index'), __('Projets'), $is('projects.*')],
                    [lroute('blog.index'), __('Blog'), $is('blog.*')],
                    [$anchor('parcours'), __('Parcours'), false],
                ] as [$href, $label, $active])
                    <a href="{{ $href }}" style="--i: {{ $loop->index }}" @class(['is-active' => $active]) @if ($active) aria-current="page" @endif>
                        <small>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</small>
                        <span>{{ $label }}</span>
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                @endforeach
            </nav>

            <div class="mobile-menu-foot" style="--i: 5">
                <a href="{{ $anchor('contact') }}" class="btn btn-accent">{{ __('Me contacter') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                @if ($profile->email)
                    <a href="mailto:{{ $profile->email }}" class="mobile-menu-mail"><i class="far fa-envelope" aria-hidden="true"></i> {{ $profile->email }}</a>
                @endif
                <x-site.socials :profile="$profile" />
            </div>
        </div>
    </div>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-meta">
                    <span>© <span data-year>{{ date('Y') }}</span> {{ $profile->name }} · {{ $profile->location }}</span>
                    @php $visitorCount = \App\Models\PageView::publicVisitorCount(); @endphp
                    <span class="visitor-count" title="{{ __('Statistiques anonymes, sans cookie publicitaire') }}">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                        {{ trans_choice(':count visiteur|:count visiteurs', $visitorCount, ['count' => number_format($visitorCount, 0, ',', ' ')]) }}
                    </span>
                </div>
                <nav class="footer-nav" aria-label="{{ __('Pied de page') }}">
                    <a href="{{ lroute('services.index') }}">{{ __('Services') }}</a>
                    <a href="{{ lroute('blog.index') }}">{{ __('Blog') }}</a>
                    <a href="{{ $anchor('contact') }}">{{ __('Contact') }}</a>
                    <a href="{{ lroute('legal.notice') }}">{{ __('Mentions légales') }}</a>
                    <a href="{{ lroute('legal.privacy') }}">{{ __('Confidentialité') }}</a>
                    @if ($analyticsId)
                        <button type="button" class="footer-link-button" data-consent-open>{{ __('Cookies') }}</button>
                    @endif
                </nav>
                <x-site.socials :profile="$profile" />
            </div>
        </div>
    </footer>

    <button type="button" class="back-to-top" aria-label="{{ __('Revenir en haut') }}"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>

    {{-- Notification affichée quand un visiteur tente de copier le contenu (voir site.js). --}}
    <div class="protect-toast" role="status" aria-live="polite" hidden
        data-msg-copy="{{ __('La copie du contenu est désactivée. Pour réutiliser un texte ou une image, contactez-moi.') }}"
        data-msg-menu="{{ __('Le contenu de ce site est protégé et réservé à la consultation en ligne.') }}"
        data-msg-image="{{ __('Les images de ce site sont protégées par le droit d’auteur.') }}"
        data-msg-keys="{{ __('Ce raccourci est désactivé sur ce site.') }}">
        <span class="protect-toast-icon" aria-hidden="true"><i class="fas fa-shield-halved"></i></span>
        <span class="protect-toast-body">
            <strong>{{ __('Contenu protégé') }}</strong>
            <span data-protect-text></span>
        </span>
        <a href="{{ $anchor('contact') }}" class="protect-toast-link">{{ __('Me contacter') }}</a>
        <button type="button" class="protect-toast-close" data-protect-close aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
    </div>

    @if ($analyticsId)
        {{-- Bandeau cookies : Google Analytics n'est chargé qu'après « Accepter » (voir site.js). --}}
        <div class="consent-banner" role="dialog" aria-live="polite" aria-labelledby="consent-title" data-ga="{{ $analyticsId }}" hidden>
            <span class="consent-icon" aria-hidden="true"><i class="fas fa-cookie-bite"></i></span>
            <div class="consent-body">
                <strong id="consent-title">{{ __('Mesure d’audience') }}</strong>
                <span>{{ __('J’aimerais utiliser Google Analytics pour savoir comment le site est consulté. Il dépose des cookies seulement si vous acceptez.') }}
                    <a href="{{ lroute('legal.privacy') }}">{{ __('En savoir plus') }}</a></span>
            </div>
            <div class="consent-actions">
                <button type="button" class="btn btn-sm consent-refuse" data-consent-refuse>{{ __('Refuser') }}</button>
                <button type="button" class="btn btn-accent btn-sm" data-consent-accept>{{ __('Accepter') }}</button>
            </div>
        </div>
    @endif

    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
