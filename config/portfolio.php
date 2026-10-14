<?php

return [

    /*
    | Langues des contenus. Les textes sont stockés par langue ({"fr": ...}) :
    | ajouter 'en' => 'English' ici suffit pour faire apparaître la
    | traduction anglaise dans les formulaires de l'administration.
    */
    'locales' => [
        'fr' => 'Français',
        'en' => 'English',
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

    /*
    | Codes de validation des outils pour webmasters (valeur « content » de la
    | balise meta fournie par Google Search Console et Bing Webmaster Tools).
    */
    'seo' => [
        'google_verification' => env('GOOGLE_SITE_VERIFICATION'),
        'bing_verification' => env('BING_SITE_VERIFICATION'),
    ],

    /*
    | Sauvegardes automatiques (commande portfolio:backup, chaque nuit à 3 h).
    | Un mot de passe chiffre les archives en AES-256 : indispensable si elles
    | sont téléchargées ou copiées ailleurs.
    */
    'backups' => [
        'path' => storage_path('app/private/backups'),
        'keep_days' => (int) env('BACKUP_KEEP_DAYS', 7),
        'keep_minimum' => 3,
        'password' => env('BACKUP_PASSWORD'),
    ],

    /*
    | Durées de conservation annoncées dans la politique de confidentialité,
    | appliquées chaque nuit par la commande portfolio:prune-data.
    */
    'retention' => [
        'contact_messages_months' => 36,
        'unpublished_reviews_months' => 6,
        'page_views_months' => 25,
        'activity_logs_months' => 12,
    ],

];
