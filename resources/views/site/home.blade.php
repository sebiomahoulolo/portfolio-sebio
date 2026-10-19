@php
    $photo = $profile->photoUrl();
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
            {!! json_ld([
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
            ]) !!}
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
                    <h1>
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
                <div class="portrait">
                    <span class="portrait-orbit" aria-hidden="true"></span>
                    <img src="{{ $photo }}" alt="{{ __('Portrait de :name', ['name' => $profile->name]) }}" width="800" height="800" fetchpriority="high">
                </div>
                <div class="portrait-id">
                    <strong>{{ $profile->name }}</strong>
                    <span>{{ $profile->translate('subtitle') }}</span>
                </div>
                @if ($profile->translate('current_position'))
                    <div class="photo-badge">
                        <i class="fas fa-briefcase" aria-hidden="true"></i>
                        <div>{{ __('Actuellement') }}<strong>{{ Str::limit($profile->translate('current_position'), 70) }}</strong></div>
                    </div>
                @endif
            </div>

            @if ($yearsOfExperience)
                @php
                    // Frise : une barre par année, du premier poste à aujourd'hui.
                    $firstYear = $experiences->min('started_at')?->year ?? now()->year;
                    $years = range(max($firstYear, now()->year - 5), now()->year);
                @endphp
                <div class="tile tile-dark stat reveal is-visible" style="--delay: .16s">
                    <span class="tile-label"><i class="fas fa-bolt" aria-hidden="true"></i> {{ __('Expérience') }}</span>
                    <div class="years" aria-hidden="true">
                        @foreach ($years as $i => $year)
                            <span @class(['is-now' => $loop->last]) style="--i: {{ $i }}; --h: {{ 28 + (int) round(72 * ($i + 1) / count($years)) }}%">
                                @if ($loop->first || $loop->last)<small>{{ $year }}</small>@endif
                            </span>
                        @endforeach
                    </div>
                    <div>
                        <div class="stat-value"><span data-count="{{ $yearsOfExperience }}">{{ $yearsOfExperience }}</span>+<small>{{ __('ans') }}</small></div>
                        <p>{{ __('à concevoir et livrer des plateformes web') }}@if ($experienceSince)<span class="stat-since">{{ __('depuis :date', ['date' => $experienceSince->translatedFormat('F Y')]) }}</span>@endif</p>
                    </div>
                </div>
            @endif

            <a href="{{ lroute('projects.index') }}" class="tile tile-copper stat reveal is-visible" style="--delay: .24s">
                <i class="fas fa-layer-group watermark" aria-hidden="true"></i>
                <span class="tile-label"><i class="fas fa-layer-group" aria-hidden="true"></i> {{ __('Réalisations') }}</span>
                <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                <div class="mini-pills" aria-hidden="true">
                    @foreach ($projects->take(5) as $project)
                        <span class="mini-pill" style="--i: {{ $loop->index }}">{{ $project->translate('title') }}</span>
                    @endforeach
                </div>
                <div>
                    <div class="stat-value"><span data-count="{{ $publishedCount }}">{{ $publishedCount }}</span></div>
                    <p>{{ __('projets en ligne et en service aujourd’hui') }}</p>
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
                        <i class="fas fa-comments watermark" aria-hidden="true"></i>
                        <span class="tile-label"><i class="fas fa-comments" aria-hidden="true"></i> {{ __('Sur mesure') }}</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            <h3>{{ __('Un besoin spécifique ?') }}</h3>
                            <p>{{ __('Décrivez-le en quelques lignes, je vous réponds sous 48 h.') }}</p>
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
                    <h2>{{ __('Développeur, formateur et fondateur d’AfriCode.') }}</h2>
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

                @if ($profile->languagesFor())
                    <div class="tile reveal" style="--delay: .12s">
                        <span class="tile-label"><i class="fas fa-language" aria-hidden="true"></i> {{ __('Langues') }}</span>
                        <ul class="list-plain">
                            @foreach ($profile->languagesFor() as $language)
                                <li><span>{{ $language['name'] }}</span><span>{{ $language['level'] }}</span></li>
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
                        <i class="fas fa-bullseye watermark" aria-hidden="true"></i>
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
                        <h2>{{ __('Les outils que j’utilise au quotidien.') }}</h2>
                    </div>
                    <p>{{ __("De l'interface à la base de données, en passant par la sécurité, les paiements en ligne et le déploiement.") }}</p>
                </div>

                {{-- Flèches animées entre les colonnes (site.js) : l'interface appelle le serveur, qui s'appuie sur les outils. --}}
                <div class="bento skills-flow" data-skills-flow>
                    @foreach (App\Models\Technology::CATEGORIES as $category => $label)
                        @continue(! $skills->has($category))
                        <div @class(['tile skill-tile reveal', 'span-2' => $loop->first, 'tile-dark' => $category === 'backend']) style="--delay: {{ $loop->index * 0.08 }}s"
                            data-flow-label="{{ ['frontend' => __('Appels API'), 'backend' => __('Intégrations')][$category] ?? '' }}">
                            <h3><i class="{{ $skillIcons[$category] }}" aria-hidden="true"></i>{{ __($label) }}</h3>
                            <ul class="skill-list">
                                @foreach ($skills[$category] as $skill)
                                    <li class="skill">
                                        <span class="skill-info">
                                            <span class="skill-name">@if ($skill->icon)<i class="{{ $skill->icon }}" aria-hidden="true"></i>@endif{{ $skill->name }}</span>
                                            {{-- Le nombre de projets n'apparaît qu'à partir de 2 : une preuve, jamais un aveu de faiblesse. --}}
                                            @if ($skill->projects_count >= App\Models\Technology::MIN_PROJECTS_SHOWN)
                                                <a href="{{ lroute('projects.index') }}?techno={{ $skill->slug }}" class="skill-projects"
                                                    title="{{ __('Voir les projets réalisés avec :name', ['name' => $skill->name]) }}">
                                                    {{ trans_choice(':count projet|:count projets', $skill->projects_count) }}<i class="fas fa-arrow-right" aria-hidden="true"></i>
                                                </a>
                                            @endif
                                        </span>
                                        @if ($skill->proficiency)
                                            <span class="skill-level is-{{ $skill->proficiency }}">{{ $skill->proficiencyLabel() }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
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
                        <i class="fas fa-folder-open watermark" aria-hidden="true"></i>
                        <span class="tile-label"><i class="fas fa-folder-open" aria-hidden="true"></i> {{ __('Galerie') }}</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            <h3>{{ __('Voir tous les projets') }}</h3>
                            <p>{{ __('Filtrez par technologie et lisez les études de cas.') }}</p>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ===================== Recommandations et avis ===================== --}}
    <section class="section" id="recommandations">
        <div class="container">
            <div class="section-head reveal">
                <div>
                    <span class="eyebrow">{{ __('Recommandations') }}</span>
                    <h2>{{ __('Ils parlent de mon travail.') }}</h2>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    @if ($profile->social('linkedin'))
                        <a href="{{ $profile->social('linkedin') }}" class="btn btn-ghost" target="_blank" rel="noopener"><i class="fab fa-linkedin" aria-hidden="true"></i> {{ __('Voir sur LinkedIn') }}</a>
                    @endif
                    <a href="{{ lroute('reviews.create') }}" class="btn btn-primary" data-magnetic><i class="fas fa-star" aria-hidden="true"></i> {{ __('Donner mon avis') }}</a>
                </div>
            </div>
            <div class="bento">
                @foreach ($testimonials->take(4) as $testimonial)
                    <x-site.testimonial-card :testimonial="$testimonial" :dark="$loop->first" :delay="($loop->index % 2) * 0.08" />
                @endforeach
                <a href="{{ lroute('reviews.create') }}" @class(['tile tile-copper stat is-compact reveal', $testimonials->take(4)->count() % 2 ? 'span-2' : 'span-4'])>
                    <i class="fas fa-star watermark" aria-hidden="true"></i>
                    <span class="tile-label"><i class="fas fa-comment-dots" aria-hidden="true"></i> {{ __('Votre avis') }}</span>
                    <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                    <div>
                        <h3>{{ __('Vous avez travaillé avec moi ?') }}</h3>
                        <p>{{ __('Partagez votre expérience en une minute. Votre avis sera publié après validation.') }}</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

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
                        <h3 class="tile-label"><i class="fas fa-briefcase" aria-hidden="true"></i> {{ __('Expérience professionnelle') }}</h3>
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
                            <h3 class="tile-label"><i class="fas fa-graduation-cap" aria-hidden="true"></i> {{ __('Formation') }}</h3>
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
