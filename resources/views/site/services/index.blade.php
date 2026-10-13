<x-layouts.site :profile="$profile"
    :title="__('Services').' — '.$profile->name"
    :description="__('Développement web, logiciels sur mesure et SaaS, applications, bases de données, conseil en sécurité et formation : les services de :name.', ['name' => $profile->name])">

    <x-slot:head>
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'ProfessionalService',
                'name' => $profile->name,
                'url' => lroute('services.index'),
                'areaServed' => ['Bénin', 'Afrique', 'International'],
                'hasOfferCatalog' => [
                    '@type' => 'OfferCatalog',
                    'name' => __('Services'),
                    'itemListElement' => $services->map(fn ($s) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $s->translate('title'), 'description' => $s->translate('summary')]])->values()->all(),
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>

    <section class="page-head">
        <div class="container bento">
            <div class="tile span-3 reveal is-visible">
                <nav class="breadcrumb" aria-label="{{ __("Fil d'Ariane") }}">
                    <a href="{{ lroute('home') }}">{{ __('Accueil') }}</a><span aria-hidden="true">/</span><span>{{ __('Services') }}</span>
                </nav>
                <h1>{{ __('Sites, SaaS, bases de données, sécurité et formation.') }}</h1>
                <p class="lead">{{ __('Je conçois, développe et sécurise vos plateformes web, et je forme vos équipes pour qu’elles restent autonomes.') }}</p>
                <div class="page-actions">
                    <a href="#contact" class="btn btn-primary" data-magnetic>{{ __('Demander un devis') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="tile tile-copper stat reveal is-visible" style="--delay: .08s">
                <i class="fas fa-layer-group watermark" aria-hidden="true"></i>
                <span class="tile-label"><i class="fas fa-layer-group" aria-hidden="true"></i> {{ __('Services') }}</span>
                <div class="mini-pills" aria-hidden="true">
                    @foreach ($services as $service)
                        <span class="mini-pill" style="--i: {{ $loop->index }}">{{ $service->translate('title') }}</span>
                    @endforeach
                </div>
                <div>
                    <div class="stat-value"><span data-count="{{ $services->count() }}">{{ $services->count() }}</span></div>
                    <p>{{ __('domaines où j’interviens, du code à la formation') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($services->isEmpty())
                <div class="tile empty">{{ __('Les services seront bientôt présentés ici.') }}</div>
            @else
                <div class="bento">
                    @foreach ($services as $service)
                        <x-site.service-card :service="$service" :detailed="true" :wide="true" :dark="$loop->index % 3 === 0" :delay="($loop->index % 2) * 0.06" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head reveal">
                <div>
                    <span class="eyebrow">{{ __('Méthode') }}</span>
                    <h2>{{ __('Une collaboration claire, en quatre étapes.') }}</h2>
                </div>
            </div>
            <x-site.process />
        </div>
    </section>

    @if ($testimonials->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('Recommandations') }}</span>
                        <h2>{{ __('Ils parlent de mon travail.') }}</h2>
                    </div>
                </div>
                <div class="bento">
                    @foreach ($testimonials as $testimonial)
                        <x-site.testimonial-card :testimonial="$testimonial" :dark="$loop->first" :span="$testimonials->count() === 1 ? 4 : 2" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-site.contact-section :profile="$profile" />
</x-layouts.site>
