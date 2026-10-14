@php
    $title = $project->translate('title');
    $facts = array_filter([
        __('Catégorie') => $project->category ? __($project->category) : null,
        __('Client') => $project->client,
        __('Période') => $project->period,
        __('En ligne') => $project->demo_url ? parse_url($project->demo_url, PHP_URL_HOST) : null,
        __('Technologies') => $project->technologies->count() ?: null,
    ]);
    $facts = array_slice($facts, 0, 3, true);
    $hasCaseStudy = filled($project->translate('description'));
@endphp

<x-layouts.site :profile="$profile"
    :title="($project->translate('seo_title') ?: $title).' — '.$profile->name"
    :description="$project->translate('seo_description') ?: $project->translate('excerpt')"
    :image="$project->coverUrl()"
    :indexable="$project->is_indexable"
    :breadcrumbs="[__('Projets') => lroute('projects.index'), $title => lroute('projects.show', ['slug' => $project->slug])]"
    type="article">

    <x-slot:head>
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'CreativeWork',
                'name' => $title,
                'description' => $project->translate('excerpt'),
                'url' => lroute('projects.show', ['slug' => $project->slug]),
                'image' => $project->coverUrl(),
                'creator' => ['@type' => 'Person', 'name' => $profile->name, 'url' => lroute('home')],
                'keywords' => $project->technologies->pluck('name')->join(', '),
                'datePublished' => $project->published_at?->toAtomString(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>

    <section class="page-head">
        <div class="container bento">
            <div class="tile tile-dark page-title span-3 reveal is-visible">
                <nav class="breadcrumb" aria-label="{{ __("Fil d'Ariane") }}">
                    <a href="{{ lroute('home') }}">{{ __('Accueil') }}</a><span aria-hidden="true">/</span>
                    <a href="{{ lroute('projects.index') }}">{{ __('Projets') }}</a><span aria-hidden="true">/</span>
                    <span>{{ $title }}</span>
                </nav>
                <h1>{{ $title }}</h1>
                @if ($project->translate('excerpt'))<p class="lead">{{ $project->translate('excerpt') }}</p>@endif
                <div class="page-actions">
                    @if ($project->demo_url)
                        <a href="{{ $project->demo_url }}" class="btn btn-accent" target="_blank" rel="noopener">{{ __('Visiter le site') }} <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                    @endif
                    @if ($project->repository_url)
                        <a href="{{ $project->repository_url }}" class="btn btn-ghost" target="_blank" rel="noopener"><i class="fab fa-github" aria-hidden="true"></i> {{ __('Code source') }}</a>
                    @endif
                </div>
            </div>

            <div class="tile reveal is-visible" style="--delay: .08s; display: grid; gap: 18px; align-content: center;">
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

    @if ($project->hasCaseStudy() || $project->metricsFor())
        <section class="section" id="etude-de-cas">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('Étude de cas') }}</span>
                        <h2>{{ __('Le contexte, le défi, ce que j’ai construit.') }}</h2>
                    </div>
                </div>
                <div class="bento">
                    @foreach ($project->metricsFor() as $metric)
                        <div @class(['tile metric-tile reveal', 'tile-copper' => $loop->first, 'tile-dark' => $loop->index === 1]) style="--delay: {{ $loop->index * 0.06 }}s">
                            <strong>{{ $metric['value'] }}</strong>
                            <span>{{ $metric['label'] }}</span>
                        </div>
                    @endforeach

                    @php
                        $sectionIcons = ['context' => 'fa-compass', 'challenge' => 'fa-triangle-exclamation', 'solution' => 'fa-screwdriver-wrench', 'results' => 'fa-chart-line'];
                        // Sections renseignées ; si leur nombre est impair, la dernière prend toute la largeur.
                        $sections = collect(App\Models\Project::CASE_STUDY_SECTIONS)->filter(fn ($label, $key) => filled($project->translate($key)));
                    @endphp
                    @foreach ($sections as $key => $label)
                        <div @class([
                            'tile case-section reveal',
                            'span-2' => ! ($loop->last && $sections->count() % 2),
                            'span-4' => $loop->last && $sections->count() % 2,
                            'tile-dark' => $key === 'solution',
                        ]) style="--delay: {{ ($loop->index % 2) * 0.06 }}s">
                            <span class="step-number"><i class="fas {{ $sectionIcons[$key] }}" aria-hidden="true"></i></span>
                            <h2>{{ __($label) }}</h2>
                            <x-site.rich-text :text="$project->translate($key)" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="container bento">
            @if ($hasCaseStudy)
                <div class="tile span-3 row-2 reveal">
                    <x-site.rich-text :text="$project->translate('description')" />
                </div>
            @endif

            @if ($project->technologies->isNotEmpty())
                <div @class(['tile reveal', 'span-2' => ! $hasCaseStudy])>
                    <span class="tile-label"><i class="fas fa-code" aria-hidden="true"></i> {{ __('Technologies') }}</span>
                    <div class="tags" style="margin-top: 14px;">
                        @foreach ($project->technologies as $tech)
                            <a class="tag" href="{{ lroute('projects.index', ['techno' => $tech->slug]) }}">{{ $tech->name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($project->translate('role'))
                <div @class(['tile reveal', 'span-4' => ! $hasCaseStudy])>
                    <span class="tile-label"><i class="fas fa-user-gear" aria-hidden="true"></i> {{ __('Mon rôle') }}</span>
                    <p style="margin-top: 12px;">{{ $project->translate('role') }}</p>
                </div>
            @endif

            <a href="{{ lroute('services.index') }}" @class(['tile tile-copper stat reveal', 'span-2' => ! $hasCaseStudy])>
                <i class="fas fa-handshake watermark" aria-hidden="true"></i>
                <span class="tile-label"><i class="fas fa-handshake" aria-hidden="true"></i> {{ __('Un projet similaire ?') }}</span>
                <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                <h3>{{ __('Découvrez mes services') }}</h3>
            </a>

            @foreach ($project->images as $image)
                <figure class="tile tile-flat gallery-tile span-2 reveal" style="margin: 0; --delay: {{ ($loop->index % 2) * 0.06 }}s">
                    <img src="{{ $image->url() }}" alt="{{ $image->translate('alt') ?? $title }}" loading="lazy" width="1600" height="1000">
                    @if ($image->translate('alt'))<figcaption>{{ $image->translate('alt') }}</figcaption>@endif
                </figure>
            @endforeach
        </div>

        @if ($previous || $next)
            <div class="container bento" style="margin-top: var(--gap);">
                @if ($previous)
                    <a href="{{ lroute('projects.show', ['slug' => $previous->slug]) }}" @class(['tile pager-tile reveal', 'span-2' => $next, 'span-4' => ! $next])>
                        <small>← {{ __('Projet précédent') }}</small><strong>{{ $previous->translate('title') }}</strong>
                    </a>
                @endif
                @if ($next)
                    <a href="{{ lroute('projects.show', ['slug' => $next->slug]) }}" @class(['tile pager-tile next reveal', 'span-2' => $previous, 'span-4' => ! $previous])>
                        <small>{{ __('Projet suivant') }} →</small><strong>{{ $next->translate('title') }}</strong>
                    </a>
                @endif
            </div>
        @endif
    </section>
</x-layouts.site>
