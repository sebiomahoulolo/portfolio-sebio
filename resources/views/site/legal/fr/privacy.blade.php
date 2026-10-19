@php $analytics = config('portfolio.seo.google_analytics'); @endphp
<p>
    @if ($analytics)
        Ce site collecte le moins de données possible. Il n’utilise <strong>aucun cookie publicitaire</strong> ni pixel Facebook ;
        Google Analytics n’est activé <strong>que si vous l’acceptez</strong>. Vos données ne sont ni vendues ni partagées.
    @else
        Ce site collecte le moins de données possible. Il n’utilise <strong>ni cookie publicitaire, ni outil de suivi tiers</strong> (pas de Google Analytics, pas de pixel Facebook),
        et ne vend ni ne partage vos données.
    @endif
</p>

<h2>Responsable du traitement</h2>
<p>
    {{ $profile->name }}{{ $profile->location ? ', '.$profile->location : '' }}{{ $profile->email ? ' · '.$profile->email : '' }}.
</p>

<h2>Données collectées et utilisation</h2>

<h3>Formulaire de contact et demande de devis</h3>
<ul>
    <li><strong>Données :</strong> nom, adresse email, et si vous les indiquez : téléphone, entreprise, budget ; ainsi que l’objet et le contenu de votre message.</li>
    <li><strong>Utilisation :</strong> répondre à votre demande et, le cas échéant, préparer un devis. Vous recevez une réponse par email ; aucune newsletter ne vous est envoyée.</li>
    <li><strong>Base :</strong> votre demande (mesures précontractuelles) et l’intérêt légitime à vous répondre.</li>
    <li><strong>Durée :</strong> {{ intdiv($retention['contact_messages_months'], 12) }} ans après réception, puis suppression automatique.</li>
</ul>

<h3>Avis déposés sur le site</h3>
<ul>
    <li><strong>Données :</strong> nom, fonction ou entreprise, note, texte de l’avis et adresse email.</li>
    <li><strong>Utilisation :</strong> l’avis est relu avant publication. Votre email n’est <strong>jamais publié</strong> : il sert uniquement à vous contacter au sujet de votre avis.</li>
    <li><strong>Base :</strong> votre consentement, que vous pouvez retirer à tout moment (l’avis est alors retiré du site).</li>
    <li><strong>Durée :</strong> tant que l’avis est publié ; un avis non publié est supprimé au bout de {{ $retention['unpublished_reviews_months'] }} mois.</li>
</ul>

<h3>Mesure d’audience</h3>
<ul>
    <li><strong>Données :</strong> page consultée, langue, site d’où vous venez (son nom de domaine seulement), pays déduit du fuseau horaire, type d’appareil, navigateur, système et temps passé sur la page.</li>
    <li><strong>Sans cookie et sans adresse IP :</strong> votre adresse IP n’est jamais enregistrée. Une empreinte chiffrée, renouvelée chaque jour, permet de compter les visiteurs sans pouvoir les suivre d’un jour à l’autre ni les identifier.</li>
    <li><strong>Do Not Track :</strong> si votre navigateur envoie ce signal, aucune mesure n’est faite.</li>
    <li><strong>Base :</strong> intérêt légitime à connaître la fréquentation du site.</li>
    <li><strong>Durée :</strong> {{ $retention['page_views_months'] }} mois, puis suppression (seul le nombre total de visiteurs est conservé).</li>
</ul>

@if ($analytics)
    <h3>Google Analytics (seulement avec votre accord)</h3>
    <ul>
        <li><strong>Quand :</strong> uniquement si vous cliquez sur « Accepter » dans le bandeau cookies. Sans accord, rien n’est chargé.</li>
        <li><strong>Données :</strong> pages vues, durée, source de la visite, appareil, pays et ville approximatifs. Google Analytics 4 n’enregistre pas votre adresse IP.</li>
        <li><strong>Cookies :</strong> <code>_ga</code> et <code>_ga_*</code>, conservés jusqu’à 2 ans, pour reconnaître un visiteur qui revient.</li>
        <li><strong>Destinataire :</strong> Google Ireland Ltd / Google LLC, avec des transferts possibles hors de votre pays, encadrés par les clauses contractuelles de Google.</li>
        <li><strong>Base :</strong> votre consentement. Vous pouvez le retirer à tout moment avec le lien « Cookies » en bas de chaque page : les cookies <code>_ga</code> sont alors supprimés.</li>
    </ul>

@endif
<h3>Mesures de sécurité techniques</h3>
<p>
    Pour limiter les abus (spam, tentatives d’intrusion), une empreinte chiffrée de l’adresse IP et le type de navigateur sont associés aux messages et aux avis.
    Ils ne permettent pas de retrouver l’adresse IP d’origine.
</p>

<h2>Cookies et stockage local</h2>
<p>Seuls des éléments strictement nécessaires au fonctionnement du site sont utilisés. Ils ne demandent pas de consentement :</p>
<ul>
    <li><strong>Cookie de session</strong> : sécurise les formulaires et la mesure d’audience pendant la visite ({{ config('session.lifetime') }} minutes d’inactivité).</li>
    <li><strong>Cookie XSRF-TOKEN</strong> : protège les formulaires contre les envois frauduleux.</li>
    <li><strong>Préférence de thème</strong> (clair ou sombre) : enregistrée dans votre navigateur uniquement, jamais envoyée au serveur.</li>
    @if ($analytics)
        <li><strong>Votre choix sur les cookies</strong> (accepté ou refusé) : enregistré dans votre navigateur pour ne pas vous redemander à chaque page.</li>
    @endif
</ul>
@if ($analytics)
    <p>Les cookies Google Analytics (<code>_ga</code>, <code>_ga_*</code>) ne sont déposés qu’avec votre accord, comme décrit plus haut.</p>
@endif

<h2>Services externes</h2>
<p>Pour afficher le site, votre navigateur charge quelques ressources depuis d’autres serveurs, qui reçoivent alors votre adresse IP :</p>
<ul>
    <li><strong>Google Fonts</strong> (polices de caractères) et <strong>cdnjs / Cloudflare</strong> (icônes) ;</li>
    <li><strong>YouTube</strong>, en mode « confidentialité renforcée », uniquement si un article contient une vidéo ;</li>
    @if ($analytics)
        <li><strong>Google Analytics</strong>, uniquement si vous l’avez accepté.</li>
    @endif
</ul>
<p>Les emails de notification sont envoyés via le serveur de messagerie de l’hébergeur, Hostinger.</p>

<h2>Qui a accès à vos données ?</h2>
<p>
    Uniquement {{ $profile->name }}, par une administration protégée par mot de passe et double authentification, et l’hébergeur Hostinger pour le stockage.
    Les données sont sauvegardées chaque nuit sur le serveur ; ces sauvegardes sont conservées {{ config('portfolio.backups.keep_days') }} jours.
</p>

<h2>Vos droits</h2>
<p>
    Vous pouvez à tout moment demander l’<strong>accès</strong> à vos données, leur <strong>rectification</strong>, leur <strong>suppression</strong>,
    la <strong>limitation</strong> de leur utilisation, vous y <strong>opposer</strong>, ou en recevoir une copie (<strong>portabilité</strong>).
    @if ($profile->email)
        Écrivez à <strong>{{ $profile->email }}</strong> : une réponse vous est apportée sous un mois au plus.
    @endif
</p>
<p>
    Si vous estimez que vos droits ne sont pas respectés, écrivez-moi en priorité : chaque demande est examinée avec attention et une solution est recherchée rapidement.
    Vous gardez par ailleurs la possibilité de saisir l’autorité de protection des données compétente dans votre pays.
</p>

<h2>Modifications</h2>
<p>Cette politique peut évoluer avec le site. La date de dernière mise à jour figure en haut de cette page.</p>
