<?php

use Illuminate\Support\Facades\Schedule;

/*
| Tâches planifiées. Sur Hostinger, une seule tâche Cron suffit (toutes les minutes) :
|   php /home/<utilisateur>/domains/<domaine>/public_html/artisan schedule:run
*/

// Sauvegarde complète chaque nuit, avec suppression des archives de plus de 7 jours.
Schedule::command('portfolio:backup')->dailyAt('03:00')->withoutOverlapping();

// Durées de conservation de la politique de confidentialité.
Schedule::command('portfolio:prune-data')->dailyAt('03:30')->withoutOverlapping();
