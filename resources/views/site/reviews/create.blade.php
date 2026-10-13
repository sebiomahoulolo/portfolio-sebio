@php
    $errors = $errors->getBag('review');
    $field = fn (string $name) => ['field', 'has-error' => $errors->has($name)];
    $rating = (int) old('rating', 5);
@endphp

<x-layouts.site :profile="$profile"
    :title="__('Donner mon avis').' — '.$profile->name"
    :description="__('Vous avez travaillé avec :name ? Partagez votre expérience.', ['name' => $profile->name])">

    <section class="page-head">
        <div class="container bento">
            <div class="tile span-3 reveal is-visible">
                <nav class="breadcrumb" aria-label="{{ __("Fil d'Ariane") }}">
                    <a href="{{ lroute('home') }}">{{ __('Accueil') }}</a><span aria-hidden="true">/</span><span>{{ __('Donner mon avis') }}</span>
                </nav>
                <h1>{{ __('Votre avis compte.') }}</h1>
                <p class="lead">{{ __('Vous avez travaillé avec moi, suivi une formation ou utilisé une de mes plateformes ? Quelques mots suffisent. Votre avis sera publié après validation.') }}</p>
            </div>
            <div class="tile tile-copper stat reveal is-visible" style="--delay: .08s">
                <i class="fas fa-star watermark" aria-hidden="true"></i>
                <span class="tile-label"><i class="fas fa-shield-halved" aria-hidden="true"></i> {{ __('Modération') }}</span>
                <div>
                    <h3 style="font-size: 1.35rem;">{{ __('Votre email reste privé') }}</h3>
                    <p style="margin-top: 8px;">{{ __('Il sert uniquement à vérifier votre avis. Il n’est jamais affiché.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="avis">
        <div class="container bento">
            <div class="tile span-2 row-2 reveal">
                @if (session('review_sent'))
                    <div class="alert alert-success" role="status">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M7.5 12.5l3 3 6-6.5"/></svg>
                        <div>{{ session('review_sent') }}</div>
                    </div>
                @elseif ($errors->any())
                    <div class="alert alert-error" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true" style="margin-top: 3px;"></i>
                        <div>{{ $errors->has('website') ? $errors->first('website') : __('Merci de vérifier les champs signalés.') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('reviews.store') }}" novalidate>
                    @csrf
                    <input type="hidden" name="locale" value="{{ app()->getLocale() }}">
                    <div class="form-grid">
                        <fieldset @class([...$field('rating'), 'full']) style="border: 0; padding: 0; margin: 0;">
                            <legend style="margin-bottom: 8px; font-size: .85rem; font-weight: 600;">{{ __('Votre note') }}</legend>
                            <div class="star-rating">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" id="rating-{{ $i }}" value="{{ $i }}" @checked($rating === $i)>
                                    <label for="rating-{{ $i }}" title="{{ trans_choice(':count étoile|:count étoiles', $i) }}">
                                        <i class="fas fa-star" aria-hidden="true"></i>
                                        <span class="sr-only">{{ trans_choice(':count étoile|:count étoiles', $i) }}</span>
                                    </label>
                                @endfor
                            </div>
                            @if ($errors->has('rating'))<div class="error">{{ $errors->first('rating') }}</div>@endif
                        </fieldset>

                        <div @class($field('author_name'))>
                            <label for="r-name">{{ __('Nom complet') }}</label>
                            <input id="r-name" name="author_name" type="text" value="{{ old('author_name') }}" autocomplete="name" required maxlength="120">
                            @if ($errors->has('author_name'))<div class="error">{{ $errors->first('author_name') }}</div>@endif
                        </div>
                        <div @class($field('author_title'))>
                            <label for="r-title">{{ __('Fonction et entreprise') }} <span class="opt">({{ __('facultatif') }})</span></label>
                            <input id="r-title" name="author_title" type="text" value="{{ old('author_title') }}" maxlength="160" placeholder="{{ __('Ex. Directeur, Entreprise') }}">
                        </div>
                        <div @class([...$field('email'), 'full'])>
                            <label for="r-email">{{ __('Email') }} <span class="opt">({{ __('non publié') }})</span></label>
                            <input id="r-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="190">
                            @if ($errors->has('email'))<div class="error">{{ $errors->first('email') }}</div>@endif
                        </div>
                        <div @class([...$field('quote'), 'full'])>
                            <label for="r-quote">{{ __('Votre avis') }}</label>
                            <textarea id="r-quote" name="quote" required minlength="20" maxlength="1500" placeholder="{{ __('Qu’avez-vous apprécié ? Quels résultats avez-vous obtenus ?') }}">{{ old('quote') }}</textarea>
                            @if ($errors->has('quote'))<div class="error">{{ $errors->first('quote') }}</div>@endif
                        </div>
                        <div @class([...$field('consent'), 'full'])>
                            <label class="check">
                                <input type="checkbox" name="consent" value="1" @checked(old('consent'))>
                                <span>{{ __('J’accepte que mon nom, ma fonction et mon avis soient publiés sur ce site.') }}</span>
                            </label>
                            @if ($errors->has('consent'))<div class="error">{{ $errors->first('consent') }}</div>@endif
                        </div>
                        <div class="hp-field" aria-hidden="true">
                            <label for="r-website">{{ __('Ne pas remplir') }}</label>
                            <input id="r-website" name="website" type="text" value="" tabindex="-1" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-footer">
                        <small>{{ __('Les avis sont relus avant publication.') }}</small>
                        <button type="submit" class="btn btn-primary" data-magnetic data-sending="{{ __('Envoi en cours…') }}">{{ __('Publier mon avis') }} <i class="fas fa-paper-plane" aria-hidden="true"></i></button>
                    </div>
                </form>
            </div>

            @foreach ($testimonials as $testimonial)
                <x-site.testimonial-card :testimonial="$testimonial" :dark="$loop->first" :delay="$loop->index * 0.08" />
            @endforeach
        </div>
    </section>
</x-layouts.site>
