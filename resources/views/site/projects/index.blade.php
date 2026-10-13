<x-layouts.site :profile="$profile"
    :title="__('Projets').($activeTechnology ? ' '.$activeTechnology->name : '').' — '.$profile->name"
    :description="__('Les réalisations de :name : plateformes web, SaaS et applications sur mesure.', ['name' => $profile->name])"
    :indexable="! $activeTechnology">

    <section class="page-head">
        <div class="container bento">
            <div class="tile span-3 reveal is-visible">
                <nav class="breadcrumb" aria-label="{{ __("Fil d'Ariane") }}">
                    <a href="{{ lroute('home') }}">{{ __('Accueil') }}</a><span aria-hidden="true">/</span><span>{{ __('Projets') }}</span>
                </nav>
                <h1>{{ __('Réalisations') }}</h1>
                <p class="lead">{{ __("Une sélection de plateformes conçues et développées de bout en bout, de l'analyse du besoin à la mise en production.") }}</p>
            </div>
            <div class="tile tile-copper stat reveal is-visible" style="--delay: .08s">
                <i class="fas fa-layer-group watermark" aria-hidden="true"></i>
                <span class="tile-label"><i class="fas fa-layer-group" aria-hidden="true"></i> {{ $activeTechnology ? $activeTechnology->name : __('Au total') }}</span>
                <div>
                    <div class="stat-value"><span data-count="{{ $projects->count() }}">{{ $projects->count() }}</span></div>
                    <p>{{ trans_choice('{0} projet présenté|{1} projet présenté|[2,*] projets présentés', $projects->count()) }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($filters->count() > 1)
                <nav class="filters reveal" aria-label="{{ __('Filtrer par technologie') }}">
                    <a href="{{ lroute('projects.index') }}" @class(['is-active' => ! $activeTechnology])>{{ __('Tous') }}</a>
                    @foreach ($filters as $filter)
                        <a href="{{ lroute('projects.index', ['techno' => $filter->slug]) }}" @class(['is-active' => $activeTechnology?->is($filter)])>{{ $filter->name }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($projects->isEmpty())
                <div class="tile empty">{{ __('Aucun projet ne correspond à ce filtre.') }}</div>
            @else
                <div class="bento">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" :wide="$loop->index % 5 === 0" :delay="($loop->index % 4) * 0.06" />
                    @endforeach
                    <a href="{{ lroute('services.index') }}" class="tile tile-dark stat span-4 reveal" style="min-height: 0;">
                        <i class="fas fa-handshake watermark" aria-hidden="true"></i>
                        <span class="tile-label"><i class="fas fa-handshake" aria-hidden="true"></i> {{ __('Et vous ?') }}</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            <h3 style="font-size: 1.5rem;">{{ __('Un projet similaire ?') }}</h3>
                            <p style="margin-top: 8px;">{{ __('Découvrez mes services et discutons de vos objectifs.') }}</p>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
