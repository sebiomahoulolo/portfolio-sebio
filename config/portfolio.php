<?php

return [

    /*
    | Langues des contenus. Les textes sont stockés par langue ({"fr": ...}) :
    | ajouter 'en' => 'English' ici suffit pour faire apparaître la
    | traduction anglaise dans les formulaires de l'administration.
    */
    'locales' => [
        'fr' => 'Français',
    ],

    'default_locale' => 'fr',

    /*
    | Adresse qui reçoit les notifications de nouveaux messages de contact.
    */
    'notify_email' => env('PORTFOLIO_NOTIFY_EMAIL') ?: 'contact@mahoulolo-sebio.com',

    /*
    | Disque sur lequel sont stockés les médias téléversés depuis
    | l'administration (images de projets, photo, CV...).
    */
    'media_disk' => 'public',

    /*
    | Nombre maximum de messages de contact par adresse IP et par heure.
    */
    'contact_rate_limit' => 5,

];
