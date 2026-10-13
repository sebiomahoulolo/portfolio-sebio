<x-layouts.site :profile="$profile"
    :title="'Projets'.($activeTechnology ? ' '.$activeTechnology->name : '').' — '.$profile->name"
    :description="'Les réalisations de '.$profile->name.' : plateformes web, sites et applications'.($activeTechnology ? ' développés avec '.$activeTechnology->name : '').'.'"
    :canonical="route('projects.index')"
    :indexable="! $activeTechnology">

    <section class="page-head">
        <div class="container bento">
            <div class="tile span-3 reveal">
                <nav class="breadcrumb" aria-label="Fil d'Ariane">
                    <a href="{{ route('home') }}">Accueil</a><span aria-hidden="true">/</span><span>Projets</span>
                </nav>
                <h1>Réalisations</h1>
                <p class="lead">Une sélection de plateformes conçues et développées de bout en bout, de l'analyse du besoin à la mise en production.</p>
            </div>
            <div class="tile tile-copper stat reveal" style="--delay: .08s">
                <span class="tile-label"><i class="fas fa-layer-group" aria-hidden="true"></i> {{ $activeTechnology ? $activeTechnology->name : 'Au total' }}</span>
                <div>
                    <div class="stat-value"><span data-count="{{ $projects->count() }}">{{ $projects->count() }}</span></div>
                    <p>projet(s) présenté(s)</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($filters->count() > 1)
                <nav class="filters reveal" aria-label="Filtrer par technologie">
                    <a href="{{ route('projects.index') }}" @class(['is-active' => ! $activeTechnology])>Tous</a>
                    @foreach ($filters as $filter)
                        <a href="{{ route('projects.index', ['techno' => $filter->slug]) }}" @class(['is-active' => $activeTechnology?->is($filter)])>{{ $filter->name }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($projects->isEmpty())
                <div class="tile empty">Aucun projet ne correspond à ce filtre.</div>
            @else
                <div class="bento">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" :wide="$loop->index % 5 === 0" :delay="($loop->index % 4) * 0.06" />
                    @endforeach
                    <a href="{{ route('home') }}#contact" class="tile tile-dark stat span-4 reveal" style="min-height: 0;">
                        <span class="tile-label"><i class="fas fa-handshake" aria-hidden="true"></i> Et vous ?</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            <h3 style="font-size: 1.5rem;">Un projet similaire ?</h3>
                            <p style="margin-top: 8px;">Discutons de vos objectifs.</p>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
