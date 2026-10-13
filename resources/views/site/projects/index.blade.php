<x-layouts.site :profile="$profile"
    :title="'Projets'.($activeTechnology ? ' '.$activeTechnology->name : '').' — '.$profile->name"
    :description="'Les réalisations de '.$profile->name.' : plateformes web, sites et applications'.($activeTechnology ? ' développés avec '.$activeTechnology->name : '').'.'"
    :canonical="route('projects.index')"
    :indexable="! $activeTechnology">

    <section class="page-hero on-dark">
        <div class="container">
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('home') }}">Accueil</a><span aria-hidden="true">/</span><span>Projets</span>
            </nav>
            <h1>Réalisations</h1>
            <p class="lead">Une sélection de plateformes conçues et développées de bout en bout, de l'analyse du besoin à la mise en production.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($filters->count() > 1)
                <nav class="filters" aria-label="Filtrer par technologie">
                    <a href="{{ route('projects.index') }}" @class(['is-active' => ! $activeTechnology])>Tous</a>
                    @foreach ($filters as $filter)
                        <a href="{{ route('projects.index', ['techno' => $filter->slug]) }}" @class(['is-active' => $activeTechnology?->is($filter)])>{{ $filter->name }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($projects->isEmpty())
                <div class="empty">Aucun projet ne correspond à ce filtre.</div>
            @else
                <div class="projects-grid">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" :delay="($loop->index % 3) * 0.08" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section section-white" style="padding-top: 0; background: transparent;">
        <div class="container">
            <div class="cta-band reveal">
                <h2>Vous avez un projet similaire ?</h2>
                <a href="{{ route('home') }}#contact" class="btn btn-primary">Discutons-en <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>
</x-layouts.site>
