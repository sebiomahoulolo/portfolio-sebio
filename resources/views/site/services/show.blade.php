@php
    $title = $service->translate('title');
@endphp

<x-layouts.site :profile="$profile"
    :title="__(':service au Bénin', ['service' => $title]).' — '.$profile->name"
    :description="meta_description($service->translate('seo_description') ?: $service->translate('summary'), __('Un service de :name, développeur Full Stack Laravel à Cotonou (Bénin). Premier échange gratuit.', ['name' => $profile->name]))"
    :breadcrumbs="[__('Services') => lroute('services.index'), $title => lroute('services.show', ['slug' => $service->slugFor()])]"
    type="article">

    <x-slot:head>
        <script type="application/ld+json">
            {!! json_ld([
                '@type' => 'Service',
                'name' => $title,
                'description' => $service->translate('summary'),
                'url' => lroute('services.show', ['slug' => $service->slugFor()]),
                'provider' => ['@type' => 'Person', 'name' => $profile->name, 'url' => lroute('home')],
                'areaServed' => ['Bénin', 'Afrique', 'International'],
            ]) !!}
        </script>
    </x-slot:head>

    <section class="page-head">
        <div class="container bento">
            <div class="tile tile-dark page-title span-3 reveal is-visible">
                <nav class="breadcrumb" aria-label="{{ __("Fil d'Ariane") }}">
                    <a href="{{ lroute('home') }}">{{ __('Accueil') }}</a><span aria-hidden="true">/</span>
                    <a href="{{ lroute('services.index') }}">{{ __('Services') }}</a><span aria-hidden="true">/</span>
                    <span>{{ $title }}</span>
                </nav>
                <h1>{{ $title }}</h1>
                <p class="lead">{{ $service->translate('summary') }}</p>
                <div class="page-actions">
                    <a href="#contact" class="btn btn-accent" data-magnetic>{{ __('Demander un devis') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    @if ($profile->whatsapp)
                        <a href="https://wa.me/{{ $profile->whatsapp }}?text={{ rawurlencode(__('Bonjour, je suis intéressé(e) par votre service : :service', ['service' => $title])) }}" class="btn btn-ghost" target="_blank" rel="noopener"><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                    @endif
                </div>
            </div>
            <div class="tile tile-copper reveal is-visible" style="--delay: .08s; display: flex; flex-direction: column; justify-content: space-between; gap: 20px;">
                <span class="big-icon" style="background: rgba(255,255,255,.18);"><i class="{{ $service->icon ?: 'fas fa-code' }}" aria-hidden="true"></i></span>
                <div class="fact">
                    <span style="color: rgba(255,255,255,.8);">{{ __('Tarif') }}</span>
                    <strong>{{ $service->translate('price_label') ?: __('Sur devis') }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container bento">
            <div class="tile span-2 row-2 reveal">
                <span class="tile-label"><i class="fas fa-circle-info" aria-hidden="true"></i> {{ __('En détail') }}</span>
                <x-site.rich-text :text="$service->translate('description')" style="margin-top: 16px;" />
            </div>
            @if ($service->featuresFor())
                <div class="tile span-2 reveal" style="--delay: .08s">
                    <span class="tile-label"><i class="fas fa-list-check" aria-hidden="true"></i> {{ __('Ce qui est inclus') }}</span>
                    <ul class="check-list" style="margin-top: 18px;">
                        @foreach ($service->featuresFor() as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <a href="#contact" class="tile tile-dark stat span-2 reveal is-compact" style="--delay: .12s">
                <i class="fas fa-handshake watermark" aria-hidden="true"></i>
                <span class="tile-label"><i class="fas fa-handshake" aria-hidden="true"></i> {{ __('Prochaine étape') }}</span>
                <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                <h2>{{ __('Parlons de votre projet : premier échange gratuit et sans engagement.') }}</h2>
            </a>
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

    @if ($projects->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('Réalisations') }}</span>
                        <h2>{{ __('Quelques projets livrés') }}</h2>
                    </div>
                    <a href="{{ lroute('projects.index') }}" class="btn btn-ghost">{{ __('Voir tous les projets') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="bento">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" :wide="$loop->first" :delay="$loop->index * 0.06" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($others->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('Services') }}</span>
                        <h2>{{ __('Autres services') }}</h2>
                    </div>
                </div>
                <div class="bento">
                    @foreach ($others->take(4) as $other)
                        <x-site.service-card :service="$other" :delay="$loop->index * 0.06" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-site.contact-section :profile="$profile" :service="$service" />
</x-layouts.site>
