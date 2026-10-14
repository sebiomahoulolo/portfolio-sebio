<x-layouts.site :profile="$profile"
    :title="$title.' — '.$profile->name"
    :description="$page === 'notice'
        ? __('Mentions légales du site de :name : éditeur, hébergeur, propriété intellectuelle et responsabilité.', ['name' => $profile->name])
        : __('Comment :name collecte, utilise et protège vos données personnelles, et comment exercer vos droits.', ['name' => $profile->name])"
    :breadcrumbs="[$title => url()->current()]">

    <section class="page-head">
        <div class="container bento">
            <div class="tile tile-dark page-title span-4 reveal is-visible">
                <nav class="breadcrumb" aria-label="{{ __("Fil d'Ariane") }}">
                    <a href="{{ lroute('home') }}">{{ __('Accueil') }}</a><span aria-hidden="true">/</span>
                    <span>{{ $title }}</span>
                </nav>
                <h1>{{ $title }}</h1>
                <p class="lead">{{ __('Dernière mise à jour : :date', ['date' => $updatedAt->translatedFormat('d F Y')]) }}</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container bento">
            <article class="tile span-3 reveal article-body">
                <div class="prose legal">
                    @include('site.legal.'.(app()->getLocale() === 'en' ? 'en' : 'fr').'.'.$page)
                </div>
            </article>

            <div class="article-aside">
                <aside class="tile reveal">
                    <span class="tile-label"><i class="fas fa-scale-balanced" aria-hidden="true"></i> {{ __('Informations légales') }}</span>
                    <nav class="legal-links" style="margin-top: 14px;">
                        <a href="{{ lroute('legal.notice') }}" @class(['is-active' => $page === 'notice'])>{{ __('Mentions légales') }}</a>
                        <a href="{{ lroute('legal.privacy') }}" @class(['is-active' => $page === 'privacy'])>{{ __('Politique de confidentialité') }}</a>
                    </nav>
                </aside>
                @if ($profile->email)
                    <aside class="tile reveal" style="--delay: .06s">
                        <span class="tile-label"><i class="far fa-envelope" aria-hidden="true"></i> {{ __('Une question ?') }}</span>
                        <p style="margin-top: 12px;">{{ __('Pour toute demande liée à vos données ou à ce site :') }}</p>
                        <a href="mailto:{{ $profile->email }}" class="legal-mail"><i class="far fa-envelope" aria-hidden="true"></i> {{ $profile->email }}</a>
                    </aside>
                @endif
            </div>
        </div>
    </section>
</x-layouts.site>
