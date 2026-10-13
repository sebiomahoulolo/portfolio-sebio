@php
    $title = $project->translate('title');
    $facts = array_filter([
        'Catégorie' => $project->category,
        'Client' => $project->client,
        'Période' => $project->period,
    ]);
    $hasCaseStudy = filled($project->translate('description')) || $project->images->isNotEmpty();
@endphp

<x-layouts.site :profile="$profile"
    :title="($project->translate('seo_title') ?: $title).' — '.$profile->name"
    :description="$project->translate('seo_description') ?: $project->translate('excerpt')"
    :image="$project->coverUrl()"
    :indexable="$project->is_indexable"
    type="article">

    <x-slot:head>
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'CreativeWork',
                'name' => $title,
                'description' => $project->translate('excerpt'),
                'url' => route('projects.show', $project),
                'image' => $project->coverUrl(),
                'creator' => ['@type' => 'Person', 'name' => $profile->name, 'url' => route('home')],
                'keywords' => $project->technologies->pluck('name')->join(', '),
                'datePublished' => $project->published_at?->toAtomString(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>

    <section class="page-hero on-dark">
        <div class="container">
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('home') }}">Accueil</a><span aria-hidden="true">/</span>
                <a href="{{ route('projects.index') }}">Projets</a><span aria-hidden="true">/</span>
                <span>{{ $title }}</span>
            </nav>
            <h1>{{ $title }}</h1>
            @if ($project->translate('excerpt'))
                <p class="lead">{{ $project->translate('excerpt') }}</p>
            @endif
            <div class="hero-actions">
                @if ($project->demo_url)
                    <a href="{{ $project->demo_url }}" class="btn btn-primary" target="_blank" rel="noopener">Visiter le site <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                @endif
                @if ($project->repository_url)
                    <a href="{{ $project->repository_url }}" class="btn btn-ghost" target="_blank" rel="noopener"><i class="fab fa-github" aria-hidden="true"></i> Code source</a>
                @endif
            </div>
            @if ($facts)
                <dl class="project-facts">
                    @foreach ($facts as $label => $value)
                        <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                    @endforeach
                </dl>
            @endif
        </div>
    </section>

    @if ($project->coverUrl())
        <div class="container project-cover">
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->translate('cover_alt') ?? $title }}" width="1600" height="900">
        </div>
    @endif

    <section class="section">
        <div @class(['container', 'case-grid' => $hasCaseStudy, 'case-grid-compact' => ! $hasCaseStudy])>
            @if ($hasCaseStudy)
            <div>
                @if ($project->translate('description'))
                    <x-site.rich-text :text="$project->translate('description')" />
                @endif

                @if ($project->images->isNotEmpty())
                    <div class="gallery">
                        @foreach ($project->images as $image)
                            <figure class="reveal">
                                <img src="{{ $image->url() }}" alt="{{ $image->translate('alt') ?? $title }}" loading="lazy" width="1600" height="1000">
                                @if ($image->translate('alt'))<figcaption>{{ $image->translate('alt') }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            <aside class="case-aside">
                @if ($project->translate('role'))
                    <div class="card">
                        <div class="label">Mon rôle</div>
                        <p>{{ $project->translate('role') }}</p>
                    </div>
                @endif
                @if ($project->technologies->isNotEmpty())
                    <div class="card">
                        <div class="label">Technologies</div>
                        <div class="tags" style="padding: 0;">
                            @foreach ($project->technologies as $tech)
                                <a class="tag" href="{{ route('projects.index', ['techno' => $tech->slug]) }}">{{ $tech->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="card card-dark">
                    <div class="label">Un projet similaire ?</div>
                    <p>Parlons de vos objectifs et de la meilleure façon de les atteindre.</p>
                    <a href="{{ route('home') }}#contact" class="btn btn-primary" style="margin-top: 18px;">Me contacter</a>
                </div>
            </aside>
        </div>
    </section>

    @if ($previous || $next)
        <section class="section" style="padding-top: 0;">
            <div class="container pager">
                @if ($previous)
                    <a href="{{ route('projects.show', $previous->slug) }}"><small>← Projet précédent</small><strong>{{ $previous->translate('title') }}</strong></a>
                @endif
                @if ($next)
                    <a href="{{ route('projects.show', $next->slug) }}" class="next"><small>Projet suivant →</small><strong>{{ $next->translate('title') }}</strong></a>
                @endif
            </div>
        </section>
    @endif
</x-layouts.site>
