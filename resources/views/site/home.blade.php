@php
    $photo = $profile->avatar_path ? $profile->mediaUrl($profile->avatar_path) : asset('images/about/3.jpg');
    $skillIcons = ['frontend' => 'fas fa-laptop-code', 'backend' => 'fas fa-server', 'tools' => 'fas fa-screwdriver-wrench', 'other' => 'fas fa-cubes'];
    $period = fn ($date) => $date ? Str::ucfirst($date->translatedFormat('M Y')) : null;

    // L'accroche « Phrase 1. Phrase 2. » s'affiche sur deux lignes, la seconde en couleur.
    $tagline = $profile->translate('tagline') ?: $profile->translate('headline');
    $taglineLines = preg_split('/(?<=\.)\s+/', (string) $tagline, 2);

    $featured = (string) $profile->translate('featured_project');
    $featuredName = Str::contains($featured, ' - ') ? Str::before($featured, ' - ') : null;
    $featuredText = $featuredName ? Str::after($featured, ' - ') : $featured;
@endphp

<x-layouts.site :profile="$profile">
    <x-slot:head>
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $profile->name,
                'jobTitle' => $profile->translate('headline'),
                'description' => $profile->translate('seo_description') ?: $profile->translate('intro'),
                'email' => $profile->email ? 'mailto:'.$profile->email : null,
                'url' => lroute('home'),
                'image' => $photo,
                'address' => $profile->location ? ['@type' => 'PostalAddress', 'addressLocality' => $profile->location] : null,
                'sameAs' => array_values($profile->social_links ?? []),
                'knowsAbout' => array_merge($profile->expertiseFor(), $stack->pluck('name')->all()),
                'makesOffer' => $services->map(fn ($s) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $s->translate('title')]])->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>

    {{-- ===================== Accueil ===================== --}}
    <section class="hero" id="accueil">
        <div class="container bento">
            <div class="tile intro span-2 row-2 reveal is-visible">
                <div class="intro-top">
                    <span @class(['availability', 'is-busy' => ! $profile->is_available])>
                        <span class="dot" aria-hidden="true"></span>
                        {{ $profile->is_available ? __('Disponible pour de nouveaux projets') : __('Agenda complet pour le moment') }}
                    </span>
                </div>
                <div>
                    <p class="intro-name"><strong>{{ $profile->name }}</strong> · {{ $profile->translate('headline') }}</p>
                    <h1 style="margin-top: 14px;">
                        <span class="line"><span>{{ $taglineLines[0] }}</span></span>
                        @isset($taglineLines[1])<span class="line"><span class="accent">{{ $taglineLines[1] }}</span></span>@endisset
                    </h1>
                </div>
                @if ($profile->translate('intro'))
                    <p class="lead">{{ $profile->translate('intro') }}</p>
                @endif
                @if ($profile->expertiseFor())
                    <div class="chips" aria-label="{{ __('Expertises') }}">
                        @foreach ($profile->expertiseFor() as $expertise)
                            <span class="chip"><i class="fas fa-check" aria-hidden="true"></i>{{ $expertise }}</span>
                        @endforeach
                    </div>
                @endif
                <div class="intro-actions">
                    <a href="{{ lroute('services.index') }}" class="btn btn-primary" data-magnetic>{{ __('Découvrir mes services') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    <a href="#projets" class="btn btn-ghost">{{ __('Voir mes réalisations') }}</a>
                </div>
            </div>

            <div class="tile photo-tile row-2 reveal is-visible" style="--delay: .08s">
                <img src="{{ $photo }}" alt="{{ __('Portrait de :name', ['name' => $profile->name]) }}" width="810" height="1080" fetchpriority="high">
                @if ($profile->translate('current_position'))
                    <div class="photo-badge">
                        <i class="fas fa-briefcase" aria-hidden="true"></i>
                        <div>{{ __('Actuellement') }}<strong>{{ Str::limit($profile->translate('current_position'), 70) }}</strong></div>
                    </div>
                @endif
            </div>

            @if ($yearsOfExperience)
                <div class="tile tile-dark stat reveal is-visible" style="--delay: .16s">
                    <span class="tile-label"><i class="fas fa-bolt" aria-hidden="true"></i> {{ __('Expérience') }}</span>
                    <div>
                        <div class="stat-value"><span data-count="{{ $yearsOfExperience }}">{{ $yearsOfExperience }}</span>+<small>{{ __('ans') }}</small></div>
                        <p>{{ __('à concevoir et livrer des plateformes web') }}</p>
                    </div>
                </div>
            @endif

            <a href="{{ lroute('projects.index') }}" class="tile tile-copper stat reveal is-visible" style="--delay: .24s">
                <span class="tile-label"><i class="fas fa-layer-group" aria-hidden="true"></i> {{ __('Réalisations') }}</span>
                <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                <div>
                    <div class="stat-value"><span data-count="{{ $publishedCount }}">{{ $publishedCount }}</span></div>
                    <p>{{ __("projets en ligne, de l'idée à la production") }}</p>
                </div>
            </a>

            <div class="tile code-tile span-2 reveal" aria-label="{{ __('Exemple de code') }}">
                <div class="code-bar"><i></i><i></i><i></i><span>platform.php</span></div>
<pre class="code" data-code><template><span class="c">// {{ __('Des plateformes SaaS sécurisées, module par module') }}</span>
<span class="v">$platform</span> = <span class="f">Platform</span>::<span class="f">create</span>(<span class="s">'AfriCode'</span>)
    -&gt;<span class="f">multiTenant</span>()
    -&gt;<span class="f">withSso</span>([<span class="s">'google'</span>, <span class="s">'microsoft'</span>])
    -&gt;<span class="f">withRoles</span>([<span class="s">'admin'</span>, <span class="s">'manager'</span>, <span class="s">'user'</span>])
    -&gt;<span class="f">secure</span>();

<span class="v">$mahoulolo</span>-&gt;<span class="f">ship</span>(<span class="v">$platform</span>)-&gt;<span class="f">train</span>(<span class="s">'next-gen devs'</span>);</template><span class="c">// {{ __('Des plateformes SaaS sécurisées, module par module') }}</span>
<span class="v">$platform</span> = <span class="f">Platform</span>::<span class="f">create</span>(<span class="s">'AfriCode'</span>)
    -&gt;<span class="f">multiTenant</span>()
    -&gt;<span class="f">withSso</span>([<span class="s">'google'</span>, <span class="s">'microsoft'</span>])
    -&gt;<span class="f">withRoles</span>([<span class="s">'admin'</span>, <span class="s">'manager'</span>, <span class="s">'user'</span>])
    -&gt;<span class="f">secure</span>();

<span class="v">$mahoulolo</span>-&gt;<span class="f">ship</span>(<span class="v">$platform</span>)-&gt;<span class="f">train</span>(<span class="s">'next-gen devs'</span>);</pre>
            </div>

            <div class="tile stat reveal" style="--delay: .08s">
                <span class="tile-label"><i class="fas fa-location-dot" aria-hidden="true"></i> {{ __('Basé au Bénin') }}</span>
                <div>
                    <div class="clock" data-clock="{{ config('app.timezone') }}">{{ now()->format('H') }}<span class="sep">:</span>{{ now()->format('i') }}</div>
                    <p class="place">{{ Str::before($profile->location ?? 'Cotonou', ',') }}<small>{{ __('Sur site, hybride ou à distance') }}</small></p>
                </div>
            </div>

            <div class="tile tile-dark links-tile reveal" style="--delay: .16s">
                <span class="tile-label"><i class="fas fa-file-lines" aria-hidden="true"></i> {{ __('Mon parcours') }}</span>
                <a href="{{ route('cv') }}" class="btn btn-accent" target="_blank" rel="noopener"><i class="fas fa-download" aria-hidden="true"></i> {{ __('Télécharger le CV') }}</a>
                <x-site.socials :profile="$profile" />
            </div>

            @if ($stack->isNotEmpty())
                <div class="tile stack-band span-4 reveal">
                    <span class="tile-label"><i class="fas fa-code" aria-hidden="true"></i> {{ __('Ma stack') }}</span>
                    <div class="marquee" aria-hidden="true">
                        <div class="marquee-track">
                            @foreach ([1, 2] as $_)
                                @foreach ($stack as $tech)
                                    <span class="tech-pill">@if ($tech->icon)<i class="{{ $tech->icon }}"></i>@endif{{ $tech->name }}</span>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    <p class="sr-only">{{ $stack->pluck('name')->join(', ') }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ===================== Services ===================== --}}
    @if ($services->isNotEmpty())
        <section class="section" id="services">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('Services') }}</span>
                        <h2>{{ __('Ce que je peux construire pour vous.') }}</h2>
                    </div>
                    <a href="{{ lroute('services.index') }}" class="btn btn-ghost">{{ __('Tous les services') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                <div class="bento">
                    @foreach ($services as $service)
                        <x-site.service-card :service="$service" :dark="$loop->first" :detailed="$loop->first"
                            :wide="$loop->first" :delay="($loop->index % 4) * 0.06" />
                    @endforeach
                    <a href="#contact" class="tile tile-copper stat reveal">
                        <span class="tile-label"><i class="fas fa-comments" aria-hidden="true"></i> {{ __('Sur mesure') }}</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            <h3 style="font-size: 1.35rem;">{{ __('Un besoin spécifique ?') }}</h3>
                            <p style="margin-top: 8px;">{{ __('Décrivez-le, je vous propose une solution.') }}</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== À propos ===================== --}}
    <section class="section" id="a-propos">
        <div class="container">
            <div class="section-head reveal">
                <div>
                    <span class="eyebrow">{{ __('À propos') }}</span>
                    <h2>{{ __('Des plateformes utiles, robustes et pensées pour durer.') }}</h2>
                </div>
            </div>

            <div class="bento">
                <div class="tile about-text span-2 row-2 reveal">
                    <span class="tile-label"><i class="fas fa-user" aria-hidden="true"></i> {{ __('Qui suis-je ?') }}</span>
                    <h3>{{ $profile->translate('headline') }} · {{ $profile->translate('subtitle') }}</h3>
                    @if ($profile->translate('about'))<p>{{ $profile->translate('about') }}</p>@endif
                    @if ($profile->translate('current_position'))
                        <p><strong>{{ __("Aujourd'hui :") }}</strong> {{ $profile->translate('current_position') }}.</p>
                    @endif
                </div>

                @if ($featured)
                    <a @if ($profile->featured_project_url) href="{{ $profile->featured_project_url }}" target="_blank" rel="noopener" @endif
                        class="tile tile-dark featured span-2 reveal" style="--delay: .08s">
                        <span class="tile-label"><i class="fas fa-star" aria-hidden="true"></i> {{ __('Projet phare') }}</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            @if ($featuredName)<h3>{{ $featuredName }}</h3>@endif
                            <p>{{ Str::ucfirst($featuredText) }}</p>
                        </div>
                    </a>
                @endif

                @if (! empty($profile->spoken_languages))
                    <div class="tile reveal" style="--delay: .12s">
                        <span class="tile-label"><i class="fas fa-language" aria-hidden="true"></i> {{ __('Langues') }}</span>
                        <ul class="list-plain">
                            @foreach ($profile->spoken_languages as $language)
                                <li><span>{{ __($language['name']) }}</span><span>{{ __($language['level']) }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($profile->interestsFor())
                    <div class="tile reveal" style="--delay: .18s">
                        <span class="tile-label"><i class="fas fa-heart" aria-hidden="true"></i> {{ __("Centres d'intérêt") }}</span>
                        <div class="chips">
                            @foreach ($profile->interestsFor() as $interest)
                                <span class="chip">{{ $interest }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($profile->translate('objective'))
                    <div class="tile tile-copper mission span-4 reveal">
                        <span class="tile-label"><i class="fas fa-bullseye" aria-hidden="true"></i> {{ __('Ma mission') }}</span>
                        <blockquote>{{ $profile->translate('objective') }}</blockquote>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ===================== Compétences ===================== --}}
    @if ($skills->isNotEmpty())
        <section class="section" id="competences">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('Compétences') }}</span>
                        <h2>{{ __('Une stack maîtrisée de bout en bout.') }}</h2>
                    </div>
                    <p>{{ __("De l'interface à la base de données, en passant par la sécurité, les paiements en ligne et le déploiement.") }}</p>
                </div>

                <div class="bento">
                    @foreach (App\Models\Technology::CATEGORIES as $category => $label)
                        @continue(! $skills->has($category))
                        <div @class(['tile skill-tile reveal', 'span-2' => $loop->first, 'tile-dark' => $category === 'backend']) style="--delay: {{ $loop->index * 0.08 }}s">
                            <h3><i class="{{ $skillIcons[$category] }}" aria-hidden="true"></i>{{ __($label) }}</h3>
                            @foreach ($skills[$category] as $skill)
                                <div class="skill">
                                    <div class="skill-top">
                                        <span>@if ($skill->icon)<i class="{{ $skill->icon }}" aria-hidden="true"></i>@endif{{ $skill->name }}</span>
                                        @if ($skill->level !== null)<span>{{ $skill->level }} %</span>@endif
                                    </div>
                                    @if ($skill->level !== null)
                                        <div class="skill-bar" role="progressbar" aria-label="{{ $skill->name }}" aria-valuenow="{{ $skill->level }}" aria-valuemin="0" aria-valuemax="100">
                                            <span style="--level: {{ $skill->level }}%"></span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== Projets ===================== --}}
    <section class="section" id="projets">
        <div class="container">
            <div class="section-head reveal">
                <div>
                    <span class="eyebrow">{{ __('Réalisations') }}</span>
                    <h2>{{ __('Projets sélectionnés') }}</h2>
                </div>
                <p>{{ __('Plateformes éducatives, emploi, immobilier, paiement en ligne : des produits utilisés au quotidien.') }}</p>
            </div>

            @if ($projects->isEmpty())
                <div class="tile empty">{{ __('Les projets seront bientôt présentés ici.') }}</div>
            @else
                <div class="bento">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" :wide="$loop->first" :delay="($loop->index % 4) * 0.06" />
                    @endforeach
                    <a href="{{ lroute('projects.index') }}" class="tile tile-dark stat reveal">
                        <span class="tile-label"><i class="fas fa-folder-open" aria-hidden="true"></i> {{ __('Galerie') }}</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            <h3 style="font-size: 1.45rem;">{{ __('Voir tous les projets') }}</h3>
                            <p style="margin-top: 8px;">{{ __('Filtrez par technologie et lisez les études de cas.') }}</p>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ===================== Recommandations ===================== --}}
    @if ($testimonials->isNotEmpty())
        <section class="section" id="recommandations">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('Recommandations') }}</span>
                        <h2>{{ __('Ils parlent de mon travail.') }}</h2>
                    </div>
                    @if ($profile->social('linkedin'))
                        <a href="{{ $profile->social('linkedin') }}" class="btn btn-ghost" target="_blank" rel="noopener"><i class="fab fa-linkedin" aria-hidden="true"></i> {{ __('Voir sur LinkedIn') }}</a>
                    @endif
                </div>
                <div class="bento">
                    @foreach ($testimonials as $testimonial)
                        <x-site.testimonial-card :testimonial="$testimonial" :dark="$loop->first"
                            :span="$testimonials->count() === 1 ? 4 : 2" :delay="($loop->index % 2) * 0.08" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== Parcours ===================== --}}
    @if ($experiences->isNotEmpty() || $educations->isNotEmpty())
        <section class="section" id="parcours">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('Parcours') }}</span>
                        <h2>{{ __('Expérience et formation') }}</h2>
                    </div>
                </div>

                <div class="bento">
                    <div class="tile span-2 row-2 reveal">
                        <span class="tile-label"><i class="fas fa-briefcase" aria-hidden="true"></i> {{ __('Expérience professionnelle') }}</span>
                        <ol class="timeline">
                            @foreach ($experiences as $experience)
                                <li>
                                    <div class="period">
                                        {{ $period($experience->started_at) }} — {{ $experience->isCurrent() ? __("aujourd'hui") : $period($experience->ended_at) }}
                                        @if ($experience->isCurrent())<span class="current-badge">{{ __('En cours') }}</span>@endif
                                    </div>
                                    <h4>{{ $experience->translate('title') }}</h4>
                                    <div class="org">{{ $experience->company }}@if ($experience->location) · {{ $experience->location }}@endif</div>
                                    @if ($experience->translate('description'))<p>{{ $experience->translate('description') }}</p>@endif
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    @if ($educations->isNotEmpty())
                        <div class="tile tile-dark span-2 reveal" style="--delay: .08s">
                            <span class="tile-label"><i class="fas fa-graduation-cap" aria-hidden="true"></i> {{ __('Formation') }}</span>
                            <ol class="timeline">
                                @foreach ($educations as $education)
                                    <li>
                                        <div class="period">{{ collect([$education->start_year, $education->end_year])->filter()->join(' — ') }}</div>
                                        <h4>{{ $education->translate('degree') }}</h4>
                                        <div class="org">@if ($education->translate('field')){{ $education->translate('field') }} · @endif{{ $education->institution }}</div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    @if ($certifications->isNotEmpty())
                        <div class="tile span-2 reveal" style="--delay: .12s">
                            <span class="tile-label"><i class="fas fa-award" aria-hidden="true"></i> {{ __('Certifications') }}</span>
                            <ul class="cert-list">
                                @foreach ($certifications as $certification)
                                    <li>
                                        <strong>{{ $certification->translate('name') }}</strong>
                                        <span>{{ $certification->issuer }}@if ($certification->issued_at) · {{ $period($certification->issued_at) }}@endif</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <x-site.contact-section :profile="$profile" />
</x-layouts.site>
