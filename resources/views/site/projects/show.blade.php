@php
    $title = $project->translate('title');
    $facts = array_filter([
        'Catégorie' => $project->category,
        'Client' => $project->client,
        'Période' => $project->period,
        'En ligne' => $project->demo_url ? parse_url($project->demo_url, PHP_URL_HOST) : null,
        'Technologies' => $project->technologies->count() ?: null,
    ]);
    $facts = array_slice($facts, 0, 3, true);
    $hasCaseStudy = filled($project->translate('description'));
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

    <section class="page-head">
        <div class="container bento">
            <div class="tile tile-dark page-title span-3 reveal">
                <nav class="breadcrumb" aria-label="Fil d'Ariane">
                    <a href="{{ route('home') }}">Accueil</a><span aria-hidden="true">/</span>
                    <a href="{{ route('projects.index') }}">Projets</a><span aria-hidden="true">/</span>
                    <span>{{ $title }}</span>
                </nav>
                <h1>{{ $title }}</h1>
                @if ($project->translate('excerpt'))<p class="lead">{{ $project->translate('excerpt') }}</p>@endif
                <div class="page-actions">
                    @if ($project->demo_url)
                        <a href="{{ $project->demo_url }}" class="btn btn-accent" target="_blank" rel="noopener">Visiter le site <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                    @endif
                    @if ($project->repository_url)
                        <a href="{{ $project->repository_url }}" class="btn btn-ghost" target="_blank" rel="noopener"><i class="fab fa-github" aria-hidden="true"></i> Code source</a>
                    @endif
                </div>
            </div>

            <div class="tile reveal" style="--delay: .08s; display: grid; gap: 18px; align-content: center;">
                @foreach ($facts as $label => $value)
                    <div class="fact"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
                @endforeach
            </div>

            @if ($project->coverUrl())
                <div class="tile tile-flat cover-tile span-4 reveal">
                    <img src="{{ $project->coverUrl() }}" alt="{{ $project->translate('cover_alt') ?? $title }}" width="1600" height="800">
                </div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="container bento">
            @if ($hasCaseStudy)
                <div class="tile span-3 row-2 reveal">
                    <x-site.rich-text :text="$project->translate('description')" />
                </div>
            @endif

            @if ($project->technologies->isNotEmpty())
                <div @class(['tile reveal', 'span-2' => ! $hasCaseStudy])>
                    <span class="tile-label"><i class="fas fa-code" aria-hidden="true"></i> Technologies</span>
                    <div class="tags" style="margin-top: 14px;">
                        @foreach ($project->technologies as $tech)
                            <a class="tag" href="{{ route('projects.index', ['techno' => $tech->slug]) }}">{{ $tech->name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($project->translate('role'))
                <div @class(['tile reveal', 'span-4' => ! $hasCaseStudy])>
                    <span class="tile-label"><i class="fas fa-user-gear" aria-hidden="true"></i> Mon rôle</span>
                    <p style="margin-top: 12px;">{{ $project->translate('role') }}</p>
                </div>
            @endif

            <a href="{{ route('home') }}#contact" @class(['tile tile-copper stat reveal', 'span-2' => ! $hasCaseStudy])>
                <span class="tile-label"><i class="fas fa-handshake" aria-hidden="true"></i> Un projet similaire ?</span>
                <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                <h3 style="font-size: 1.4rem;">Parlons de vos objectifs</h3>
            </a>

            @foreach ($project->images as $image)
                <figure @class(['tile tile-flat gallery-tile reveal', 'span-2' => true]) style="margin: 0; --delay: {{ ($loop->index % 2) * 0.06 }}s">
                    <img src="{{ $image->url() }}" alt="{{ $image->translate('alt') ?? $title }}" loading="lazy" width="1600" height="1000">
                    @if ($image->translate('alt'))<figcaption>{{ $image->translate('alt') }}</figcaption>@endif
                </figure>
            @endforeach
        </div>

        @if ($previous || $next)
            <div class="container bento" style="margin-top: var(--gap);">
                @if ($previous)
                    <a href="{{ route('projects.show', $previous->slug) }}" @class(['tile pager-tile reveal', 'span-2' => $next, 'span-4' => ! $next])>
                        <small>← Projet précédent</small><strong>{{ $previous->translate('title') }}</strong>
                    </a>
                @endif
                @if ($next)
                    <a href="{{ route('projects.show', $next->slug) }}" @class(['tile pager-tile next reveal', 'span-2' => $previous, 'span-4' => ! $previous])>
                        <small>Projet suivant →</small><strong>{{ $next->translate('title') }}</strong>
                    </a>
                @endif
            </div>
        @endif
    </section>
</x-layouts.site>
