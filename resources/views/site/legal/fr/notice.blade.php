<h2>Éditeur du site</h2>
<p>
    Le site <strong>{{ parse_url(config('app.url'), PHP_URL_HOST) }}</strong> est édité par <strong>{{ $profile->name }}</strong>,
    développeur web indépendant{{ $profile->location ? ', basé à '.$profile->location : '' }}.
</p>
<ul>
    @if ($profile->email)<li>Email : {{ $profile->email }}</li>@endif
    @if ($profile->phone)<li>Téléphone : {{ $profile->phone }}</li>@endif
    <li>Directeur de la publication : {{ $profile->name }}</li>
</ul>

<h2>Hébergement</h2>
<p>
    Le site est hébergé par <strong>Hostinger International Ltd</strong>, 61 Lordou Vironos Street, 6023 Larnaca, Chypre
    (hostinger.com).
</p>

<h2>Propriété intellectuelle</h2>
<p>
    L’ensemble des contenus de ce site (textes, articles, études de cas, photographies, logos, éléments graphiques et code)
    est la propriété de {{ $profile->name }}, sauf mention contraire. Les marques et logos des projets présentés appartiennent à leurs titulaires respectifs.
</p>
<p>
    Toute reproduction, représentation, modification ou diffusion, totale ou partielle, sans autorisation écrite préalable est interdite.
    Pour réutiliser un contenu, écrivez à l’adresse ci-dessus : une citation courte avec un lien vers la page d’origine est généralement acceptée.
</p>

<h2>Liens vers d’autres sites</h2>
<p>
    Ce site contient des liens vers des sites externes (projets réalisés, réseaux sociaux). Ces sites ne sont pas sous le contrôle de l’éditeur,
    qui ne peut être tenu responsable de leur contenu ni de leurs pratiques en matière de données personnelles.
</p>

<h2>Responsabilité</h2>
<p>
    Les informations publiées sont fournies à titre indicatif et mises à jour régulièrement. Malgré le soin apporté, elles peuvent contenir des erreurs
    ou ne plus être à jour : signalez-les à l’adresse ci-dessus. Les tarifs indiqués ne constituent pas une offre ferme ; seul un devis signé engage les parties.
</p>

<h2>Données personnelles</h2>
<p>
    La collecte et le traitement de vos données sont décrits dans la
    <a href="{{ lroute('legal.privacy') }}">politique de confidentialité</a>.
</p>

<h2>Droit applicable</h2>
<p>
    Le présent site et ces mentions sont soumis au droit béninois, notamment à la loi n° 2017-20 du 20 avril 2018 portant Code du numérique en République du Bénin.
    En cas de litige, une solution amiable sera recherchée en priorité.
</p>
