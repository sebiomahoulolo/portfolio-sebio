@props(['profile'])

@php
    $errors = $errors->getBag('contact');
    $type = old('type', 'contact');
    $field = fn (string $name) => ['field', 'has-error' => $errors->has($name)];
@endphp

<section class="section" id="contact">
    <div class="container bento">
        <div class="tile tile-dark contact-intro span-2 reveal">
            <div>
                <span class="tile-label"><i class="fas fa-paper-plane" aria-hidden="true"></i> Contact</span>
                <h2>Un projet en tête ? Parlons-en.</h2>
                <p class="lead">Site vitrine, plateforme sur mesure, intégration de paiement ou formation : décrivez votre besoin, je reviens vers vous sous 48 heures ouvrées.</p>
            </div>

            <div class="channels">
                @if ($profile->email)
                    <a class="channel" href="mailto:{{ $profile->email }}">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <div><small>Email</small><strong>{{ $profile->email }}</strong></div>
                    </a>
                @endif
                @if ($profile->whatsapp)
                    <a class="channel" href="https://wa.me/{{ $profile->whatsapp }}" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        <div><small>WhatsApp</small><strong>{{ $profile->phone ?: '+'.$profile->whatsapp }}</strong></div>
                    </a>
                @endif
                @if ($profile->social('linkedin'))
                    <a class="channel" href="{{ $profile->social('linkedin') }}" target="_blank" rel="noopener">
                        <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                        <div><small>LinkedIn</small><strong>Voir mon profil</strong></div>
                    </a>
                @endif
            </div>
        </div>

        <div class="tile span-2 reveal" style="--delay: .08s">
            @if (session('contact_sent'))
                <div class="alert alert-success" role="status">
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                    <div>{{ session('contact_sent') }}</div>
                </div>
            @elseif ($errors->any())
                <div class="alert alert-error" role="alert">
                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                    <div>{{ $errors->has('website') ? $errors->first('website') : 'Merci de vérifier les champs signalés.' }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" data-contact-form novalidate>
                @csrf
                <div class="form-grid">
                    <fieldset class="full" style="border: 0; padding: 0; margin: 0;">
                        <legend class="sr-only">Type de demande</legend>
                        <div class="type-switch">
                            @foreach (App\Models\ContactMessage::TYPES as $value => $label)
                                <input type="radio" name="type" id="type-{{ $value }}" value="{{ $value }}" @checked($type === $value)>
                                <label for="type-{{ $value }}">{{ $label }}</label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div @class($field('name'))>
                        <label for="c-name">Nom complet</label>
                        <input id="c-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required maxlength="120">
                        @if ($errors->has('name'))<div class="error">{{ $errors->first('name') }}</div>@endif
                    </div>
                    <div @class($field('email'))>
                        <label for="c-email">Email</label>
                        <input id="c-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="190">
                        @if ($errors->has('email'))<div class="error">{{ $errors->first('email') }}</div>@endif
                    </div>
                    <div @class($field('phone'))>
                        <label for="c-phone">Téléphone <span class="opt">(facultatif)</span></label>
                        <input id="c-phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="40">
                        @if ($errors->has('phone'))<div class="error">{{ $errors->first('phone') }}</div>@endif
                    </div>
                    <div @class($field('company'))>
                        <label for="c-company">Entreprise <span class="opt">(facultatif)</span></label>
                        <input id="c-company" name="company" type="text" value="{{ old('company') }}" autocomplete="organization" maxlength="160">
                    </div>
                    <div @class([...$field('subject'), 'full'])>
                        <label for="c-subject">Sujet</label>
                        <input id="c-subject" name="subject" type="text" value="{{ old('subject') }}" required maxlength="190">
                        @if ($errors->has('subject'))<div class="error">{{ $errors->first('subject') }}</div>@endif
                    </div>
                    <div @class([...$field('budget'), 'full']) data-budget-field @if ($type !== 'quote') hidden @endif>
                        <label for="c-budget">Budget estimé <span class="opt">(facultatif)</span></label>
                        <select id="c-budget" name="budget">
                            <option value="">Sélectionnez une fourchette</option>
                            @foreach (App\Http\Requests\ContactRequest::BUDGETS as $value => $label)
                                <option value="{{ $value }}" @selected(old('budget') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div @class([...$field('message'), 'full'])>
                        <label for="c-message">Votre message</label>
                        <textarea id="c-message" name="message" required minlength="20" maxlength="5000" placeholder="Contexte, objectifs, délais...">{{ old('message') }}</textarea>
                        @if ($errors->has('message'))<div class="error">{{ $errors->first('message') }}</div>@endif
                    </div>
                    <div class="hp-field" aria-hidden="true">
                        <label for="c-website">Ne pas remplir</label>
                        <input id="c-website" name="website" type="text" value="" tabindex="-1" autocomplete="off">
                    </div>
                </div>

                <div class="form-footer">
                    <small>Vos informations servent uniquement à répondre à votre demande.</small>
                    <button type="submit" class="btn btn-primary">Envoyer le message <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                </div>
            </form>
        </div>
    </div>
</section>
