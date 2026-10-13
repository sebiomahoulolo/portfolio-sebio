@php
    $nameParts = explode(' ', $profile->name, 2);
    $photo = $profile->avatar_path ? $profile->mediaUrl($profile->avatar_path) : asset('images/about/3.jpg');
    $skillIcons = ['frontend' => 'fas fa-laptop-code', 'backend' => 'fas fa-server', 'tools' => 'fas fa-screwdriver-wrench', 'other' => 'fas fa-cubes'];
    $months = ['', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $formatDate = fn ($date) => $date ? $months[$date->month].' '.$date->year : null;
    $typedPhrases = array_values(array_filter([$profile->translate('headline'), $profile->translate('subtitle')]));
    $half = (int) ceil($stack->count() / 2);
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
                'url' => route('home'),
                'image' => $photo,
                'address' => $profile->location ? ['@type' => 'PostalAddress', 'addressLocality' => $profile->location] : null,
                'sameAs' => array_values($profile->social_links ?? []),
                'knowsAbout' => $stack->pluck('name')->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>

    {{-- ===================== Accueil : grille bento ===================== --}}
    <section class="hero" id="accueil">
        <div class="container bento">
            <div class="tile intro span-2 row-2 reveal">
                <span @class(['availability', 'is-busy' => ! $profile->is_available])>
                    <span class="dot" aria-hidden="true"></span>
                    {{ $profile->is_available ? 'Disponible pour de nouveaux projets' : 'Agenda complet pour le moment' }}
                </span>
                <div>
                    <h1>Bonjour <span class="wave" aria-hidden="true">👋</span><br>je suis <span class="name">{{ $nameParts[0] }}</span></h1>
                    <p class="typed" data-typed='@json($typedPhrases)'>
                        <span class="typed-text">{{ $typedPhrases[0] ?? '' }}</span><span class="caret" aria-hidden="true"></span>
                    </p>
                </div>
                @if ($profile->translate('intro'))
                    <p class="lead">{{ Str::limit($profile->translate('intro'), 230) }}</p>
                @endif
                <div class="intro-actions">
                    <a href="#projets" class="btn btn-primary">Voir mes réalisations <i class="fas fa-arrow-down" aria-hidden="true"></i></a>
                    <a href="#contact" class="btn btn-ghost">Me contacter</a>
                </div>
            </div>

            <div class="tile photo-tile row-2 reveal" style="--delay: .08s">
                <img src="{{ $photo }}" alt="Portrait de {{ $profile->name }}" width="810" height="1080" fetchpriority="high">
                @if ($profile->translate('current_position'))
                    <div class="photo-badge">
                        <i class="fas fa-briefcase" aria-hidden="true"></i>
                        <div>Actuellement<strong>{{ Str::limit($profile->translate('current_position'), 60) }}</strong></div>
                    </div>
                @endif
            </div>

            @if ($yearsOfExperience)
                <div class="tile tile-dark stat reveal" style="--delay: .16s">
                    <span class="tile-label"><i class="fas fa-bolt" aria-hidden="true"></i> Expérience</span>
                    <div>
                        <div class="stat-value"><span data-count="{{ $yearsOfExperience }}">{{ $yearsOfExperience }}</span>+<small>ans</small></div>
                        <p>à concevoir et livrer des plateformes web</p>
                    </div>
                </div>
            @endif

            <a href="{{ route('projects.index') }}" class="tile tile-copper stat reveal" style="--delay: .24s">
                <span class="tile-label"><i class="fas fa-layer-group" aria-hidden="true"></i> Réalisations</span>
                <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                <div>
                    <div class="stat-value"><span data-count="{{ $publishedCount }}">{{ $publishedCount }}</span></div>
                    <p>projets en ligne, de l'idée à la production</p>
                </div>
            </a>

            @if ($stack->isNotEmpty())
                <div class="tile stack-tile span-2 reveal">
                    <span class="tile-label"><i class="fas fa-code" aria-hidden="true"></i> Ma stack au quotidien</span>
                    <div>
                        @foreach ([$stack->take($half), $stack->slice($half)] as $index => $row)
                            <div @class(['marquee', 'reverse' => $index === 1]) aria-hidden="true">
                                <div class="marquee-track">
                                    @foreach ([1, 2] as $_)
                                        @foreach ($row as $tech)
                                            <span class="tech-pill">@if ($tech->icon)<i class="{{ $tech->icon }}"></i>@endif{{ $tech->name }}</span>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <p class="sr-only">{{ $stack->pluck('name')->join(', ') }}</p>
                    </div>
                </div>
            @endif

            <div class="tile stat reveal" style="--delay: .08s">
                <span class="tile-label"><i class="fas fa-location-dot" aria-hidden="true"></i> Basé au Bénin</span>
                <div>
                    <div class="clock" data-clock="{{ config('app.timezone') }}">{{ now()->format('H') }}<span class="sep">:</span>{{ now()->format('i') }}</div>
                    <p class="place">{{ Str::before($profile->location ?? 'Cotonou', ',') }}<small>Disponible en remote, partout dans le monde</small></p>
                </div>
            </div>

            <div class="tile tile-dark links-tile reveal" style="--delay: .16s">
                <span class="tile-label"><i class="fas fa-file-lines" aria-hidden="true"></i> Mon parcours</span>
                <a href="{{ route('cv') }}" class="btn btn-accent" target="_blank" rel="noopener"><i class="fas fa-download" aria-hidden="true"></i> Télécharger le CV</a>
                <x-site.socials :profile="$profile" />
            </div>
        </div>
    </section>

    {{-- ===================== À propos ===================== --}}
    <section class="section" id="a-propos">
        <div class="container">
            <div class="section-head reveal">
                <div>
                    <span class="eyebrow">À propos</span>
                    <h2>Des plateformes utiles, robustes et pensées pour durer.</h2>
                </div>
            </div>

            <div class="bento">
                <div class="tile about-text span-2 row-2 reveal">
                    <span class="tile-label"><i class="fas fa-user" aria-hidden="true"></i> Qui suis-je ?</span>
                    <h3>{{ $profile->translate('headline') }}</h3>
                    @if ($profile->translate('about'))<p>{{ $profile->translate('about') }}</p>@endif
                    @if ($profile->translate('current_position'))
                        <p><strong>Aujourd'hui :</strong> {{ $profile->translate('current_position') }}.</p>
                    @endif
                </div>

                @if ($profile->translate('featured_project'))
                    <a @if ($profile->featured_project_url) href="{{ $profile->featured_project_url }}" target="_blank" rel="noopener" @endif
                        class="tile tile-dark featured span-2 reveal" style="--delay: .08s">
                        <span class="tile-label"><i class="fas fa-star" aria-hidden="true"></i> Projet phare</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            <h3>{{ Str::before($profile->translate('featured_project'), ' - ') }}</h3>
                            <p style="margin-top: 10px;">{{ Str::contains($profile->translate('featured_project'), ' - ') ? Str::ucfirst(Str::after($profile->translate('featured_project'), ' - ')) : $profile->translate('featured_project') }}</p>
                        </div>
                    </a>
                @endif

                @if (! empty($profile->spoken_languages))
                    <div class="tile reveal" style="--delay: .12s">
                        <span class="tile-label"><i class="fas fa-language" aria-hidden="true"></i> Langues</span>
                        <ul class="list-plain">
                            @foreach ($profile->spoken_languages as $language)
                                <li><span>{{ $language['name'] }}</span><span>{{ $language['level'] }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($profile->interestsFor())
                    <div class="tile reveal" style="--delay: .18s">
                        <span class="tile-label"><i class="fas fa-heart" aria-hidden="true"></i> Centres d'intérêt</span>
                        <div class="chips">
                            @foreach ($profile->interestsFor() as $interest)
                                <span class="chip">{{ $interest }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($profile->translate('objective'))
                    <div class="tile tile-copper mission span-4 reveal">
                        <span class="tile-label"><i class="fas fa-bullseye" aria-hidden="true"></i> Ma mission</span>
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
                        <span class="eyebrow">Compétences</span>
                        <h2>Une stack maîtrisée de bout en bout.</h2>
                    </div>
                    <p>De l'interface à la base de données, en passant par les paiements en ligne et le déploiement.</p>
                </div>

                <div class="bento">
                    @foreach (App\Models\Technology::CATEGORIES as $category => $label)
                        @continue(! $skills->has($category))
                        <div @class(['tile skill-tile reveal', 'span-2' => $loop->first, 'tile-dark' => $category === 'backend']) style="--delay: {{ $loop->index * 0.08 }}s">
                            <h3><i class="{{ $skillIcons[$category] }}" aria-hidden="true"></i>{{ $label }}</h3>
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
                    <span class="eyebrow">Réalisations</span>
                    <h2>Projets sélectionnés</h2>
                </div>
                <p>Plateformes éducatives, emploi, immobilier, paiement en ligne : des produits utilisés au quotidien.</p>
            </div>

            @if ($projects->isEmpty())
                <div class="tile empty">Les projets seront bientôt présentés ici.</div>
            @else
                <div class="bento">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" :wide="$loop->first" :delay="($loop->index % 4) * 0.06" />
                    @endforeach
                    <a href="{{ route('projects.index') }}" class="tile tile-dark stat reveal">
                        <span class="tile-label"><i class="fas fa-folder-open" aria-hidden="true"></i> Galerie</span>
                        <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <div>
                            <h3 style="font-size: 1.5rem;">Voir tous les projets</h3>
                            <p style="margin-top: 8px;">Filtrez par technologie et lisez les études de cas.</p>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ===================== Parcours ===================== --}}
    @if ($experiences->isNotEmpty() || $educations->isNotEmpty())
        <section class="section" id="parcours">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">Parcours</span>
                        <h2>Expérience et formation</h2>
                    </div>
                </div>

                <div class="bento">
                    <div class="tile span-2 row-2 reveal">
                        <span class="tile-label"><i class="fas fa-briefcase" aria-hidden="true"></i> Expérience professionnelle</span>
                        <ol class="timeline">
                            @foreach ($experiences as $experience)
                                <li>
                                    <div class="period">
                                        {{ $formatDate($experience->started_at) }} — {{ $experience->isCurrent() ? "aujourd'hui" : $formatDate($experience->ended_at) }}
                                        @if ($experience->isCurrent())<span class="current-badge">En cours</span>@endif
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
                            <span class="tile-label"><i class="fas fa-graduation-cap" aria-hidden="true"></i> Formation</span>
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
                            <span class="tile-label"><i class="fas fa-award" aria-hidden="true"></i> Certifications</span>
                            <ul class="cert-list">
                                @foreach ($certifications as $certification)
                                    <li>
                                        <strong>{{ $certification->translate('name') }}</strong>
                                        <span>{{ $certification->issuer }}@if ($certification->issued_at) · {{ $formatDate($certification->issued_at) }}@endif</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== Contact ===================== --}}
    <x-site.contact-section :profile="$profile" />
</x-layouts.site>
