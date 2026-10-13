@php
    $steps = [
        ['fas fa-ear-listen', __('Écoute et cadrage'), __('Nous clarifions vos objectifs, vos utilisateurs et vos contraintes. Vous recevez une proposition claire : périmètre, délais et budget.')],
        ['fas fa-pen-ruler', __('Conception'), __("Architecture, modèle de données et maquettes : chaque choix est validé avec vous avant d'écrire la première ligne de code.")],
        ['fas fa-code', __('Développement et tests'), __('Livraisons régulières, démonstrations et tests automatisés : vous suivez l’avancement et rien n’arrive par surprise.')],
        ['fas fa-rocket', __('Mise en ligne et suivi'), __('Déploiement, formation de vos équipes et maintenance : votre plateforme continue d’évoluer avec vous.')],
    ];
@endphp

<div class="bento">
    @foreach ($steps as $index => [$icon, $title, $text])
        <div @class(['tile step reveal', 'tile-dark' => $index === 3]) style="--delay: {{ $index * 0.07 }}s">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="step-number">0{{ $index + 1 }}</span>
                <span class="service-icon" style="width: 42px; height: 42px; font-size: 1rem;"><i class="{{ $icon }}" aria-hidden="true"></i></span>
            </div>
            <h3>{{ $title }}</h3>
            <p>{{ $text }}</p>
        </div>
    @endforeach
</div>
