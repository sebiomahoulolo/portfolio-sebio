@php
    $nameParts = explode(' ', $profile->name, 2);
    $photo = $profile->avatar_path ? $profile->mediaUrl($profile->avatar_path) : asset('images/about/3.jpg');
    $skillIcons = ['frontend' => 'fas fa-laptop-code', 'backend' => 'fas fa-server', 'tools' => 'fas fa-screwdriver-wrench', 'other' => 'fas fa-cubes'];
    $months = ['', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $formatDate = fn ($date) => $date ? $months[$date->month].' '.$date->year : null;
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
                'knowsAbout' => $marquee->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>

    {{-- ===================== Hero ===================== --}}
    <section class="hero on-dark" id="accueil">
        <div class="container hero-grid">
            <div>
                <span @class(['availability', 'is-busy' => ! $profile->is_available])>
                    <span class="dot" aria-hidden="true"></span>
                    {{ $profile->is_available ? 'Disponible pour de nouveaux projets' : 'Agenda complet pour le moment' }}
                </span>

                <h1>{{ $nameParts[0] }} <span class="accent">{{ $nameParts[1] ?? '' }}</span></h1>
                <p class="hero-role">
                    {{ $profile->translate('headline') }}@if ($profile->translate('subtitle')) · {{ $profile->translate('subtitle') }}@endif
                </p>

                @if ($profile->translate('intro'))
                    <p class="lead">{{ $profile->translate('intro') }}</p>
                @endif

                <div class="hero-actions">
                    <a href="#projets" class="btn btn-primary">Voir mes réalisations <i class="fas fa-arrow-down" aria-hidden="true"></i></a>
                    <a href="{{ route('cv') }}" class="btn btn-ghost" target="_blank" rel="noopener"><i class="fas fa-file-arrow-down" aria-hidden="true"></i> Télécharger mon CV</a>
                </div>

                <dl class="hero-stats">
                    @if ($yearsOfExperience)
                        <div><dt>Expérience</dt><dd>{{ $yearsOfExperience }}+ ans</dd></div>
                    @endif
                    <div><dt>Projets livrés</dt><dd>{{ $publishedCount }}</dd></div>
                    @if ($profile->location)
                        <div><dt>Basé à</dt><dd>{{ Str::before($profile->location, ',') }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="portrait">
                <div class="portrait-frame">
                    <img src="{{ $photo }}" alt="Portrait de {{ $profile->name }}" width="810" height="1080" fetchpriority="high">
                </div>
                @if ($profile->translate('current_position'))
                    <div class="portrait-card">
                        <i class="fas fa-briefcase" aria-hidden="true"></i>
                        <div><small>Actuellement</small><strong>{{ Str::limit($profile->translate('current_position'), 48) }}</strong></div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($marquee->isNotEmpty())
        <div class="marquee" aria-hidden="true">
            <div class="marquee-track">
                @foreach ([1, 2] as $_)
                    @foreach ($marquee as $tech)<span>{{ $tech }}</span>@endforeach
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===================== À propos ===================== --}}
    <section class="section" id="a-propos">
        <div class="container about-grid">
            <div class="about-text reveal">
                <span class="eyebrow">À propos</span>
                <h2>Des plateformes web utiles, robustes et pensées pour durer.</h2>
                @if ($profile->translate('about'))
                    <p class="lead">{{ $profile->translate('about') }}</p>
                @endif
                @if ($profile->translate('objective'))
                    <blockquote class="mission">{{ $profile->translate('objective') }}</blockquote>
                @endif
            </div>

            <div class="about-side">
                @if ($profile->translate('featured_project'))
                    <div class="card card-dark reveal">
                        <div class="label">Projet phare</div>
                        <p>{{ $profile->translate('featured_project') }}</p>
                        @if ($profile->featured_project_url)
                            <a href="{{ $profile->featured_project_url }}" class="link-arrow" target="_blank" rel="noopener">Découvrir le projet <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        @endif
                    </div>
                @endif

                <div class="facts">
                    @if (! empty($profile->spoken_languages))
                        <div class="card reveal" style="--delay: .1s">
                            <div class="label">Langues</div>
                            <ul>
                                @foreach ($profile->spoken_languages as $language)
                                    <li><span>{{ $language['name'] }}</span><span>{{ $language['level'] }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($profile->interestsFor())
                        <div class="card reveal" style="--delay: .2s">
                            <div class="label">Centres d'intérêt</div>
                            <div class="chips">
                                @foreach ($profile->interestsFor() as $interest)
                                    <span class="chip">{{ $interest }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== Compétences ===================== --}}
    @if ($skills->isNotEmpty())
        <section class="section section-dark on-dark" id="competences">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">Compétences</span>
                        <h2>Une stack maîtrisée de bout en bout.</h2>
                    </div>
                    <p class="muted">De l'interface à la base de données, en passant par les paiements en ligne et le déploiement.</p>
                </div>

                <div class="skills-grid">
                    @foreach (App\Models\Technology::CATEGORIES as $category => $label)
                        @continue(! $skills->has($category))
                        <div class="skill-group">
                            <div class="skill-group-head">
                                <i class="{{ $skillIcons[$category] }}" aria-hidden="true"></i>
                                <h3>{{ $label }}</h3>
                            </div>
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
                <p class="muted">Plateformes éducatives, emploi, immobilier, paiement en ligne : des produits utilisés au quotidien.</p>
            </div>

            @if ($projects->isEmpty())
                <div class="empty">Les projets seront bientôt présentés ici.</div>
            @else
                <div class="projects-grid">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" :delay="($loop->index % 3) * 0.08" />
                    @endforeach
                </div>
                @if ($publishedCount > $projects->count())
                    <div class="projects-more">
                        <a href="{{ route('projects.index') }}" class="btn btn-dark">Voir les {{ $publishedCount }} projets <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                @endif
            @endif
        </div>
    </section>

    {{-- ===================== Parcours ===================== --}}
    @if ($experiences->isNotEmpty() || $educations->isNotEmpty())
        <section class="section section-white" id="parcours">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">Parcours</span>
                        <h2>Expérience et formation</h2>
                    </div>
                </div>

                <div class="career-grid">
                    <div class="reveal">
                        <h3 class="col-title"><i class="fas fa-briefcase" aria-hidden="true"></i> Expérience professionnelle</h3>
                        <ol class="timeline">
                            @foreach ($experiences as $experience)
                                <li>
                                    <div class="period">
                                        {{ $formatDate($experience->started_at) }} — {{ $experience->isCurrent() ? "aujourd'hui" : $formatDate($experience->ended_at) }}
                                        @if ($experience->isCurrent())<span class="current-badge">En poste</span>@endif
                                    </div>
                                    <h4>{{ $experience->translate('title') }}</h4>
                                    <div class="org">{{ $experience->company }}@if ($experience->location) · {{ $experience->location }}@endif</div>
                                    @if ($experience->translate('description'))
                                        <p>{{ $experience->translate('description') }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="reveal" style="--delay: .1s">
                        <h3 class="col-title"><i class="fas fa-graduation-cap" aria-hidden="true"></i> Formation</h3>
                        <ol class="timeline">
                            @foreach ($educations as $education)
                                <li>
                                    <div class="period">{{ collect([$education->start_year, $education->end_year])->filter()->join(' — ') }}</div>
                                    <h4>{{ $education->translate('degree') }}</h4>
                                    <div class="org">
                                        @if ($education->translate('field')){{ $education->translate('field') }} · @endif{{ $education->institution }}
                                    </div>
                                </li>
                            @endforeach
                        </ol>

                        @if ($certifications->isNotEmpty())
                            <ul class="cert-list">
                                @foreach ($certifications as $certification)
                                    <li>
                                        <i class="fas fa-certificate" aria-hidden="true"></i>
                                        <div>
                                            <strong>{{ $certification->translate('name') }}</strong>
                                            <span>{{ $certification->issuer }}@if ($certification->issued_at) · {{ $formatDate($certification->issued_at) }}@endif</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== Contact ===================== --}}
    <x-site.contact-section :profile="$profile" />
</x-layouts.site>
