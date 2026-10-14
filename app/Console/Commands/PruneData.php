<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\PageView;
use App\Models\ProfileSetting;
use App\Models\Testimonial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Applique les durées de conservation annoncées dans la politique de confidentialité.
 */
class PruneData extends Command
{
    protected $signature = 'portfolio:prune-data';

    protected $description = 'Supprime les données personnelles dont la durée de conservation est dépassée';

    public function handle(): int
    {
        $retention = config('portfolio.retention');

        $messages = ContactMessage::where('created_at', '<', now()->subMonths($retention['contact_messages_months']))->delete();

        // Avis jamais publiés (en attente ou refusés) : l'email n'a plus de raison d'être conservé.
        $reviews = Testimonial::whereIn('status', ['pending', 'rejected'])
            ->where('created_at', '<', now()->subMonths($retention['unpublished_reviews_months']))
            ->delete();

        $logs = ActivityLog::where('created_at', '<', now()->subMonths($retention['activity_logs_months']))->delete();

        $views = $this->prunePageViews(now()->subMonths($retention['page_views_months'])->startOfDay());

        $this->info("Messages : {$messages} · Avis non publiés : {$reviews} · Mesures d'audience : {$views} · Journal : {$logs}");

        return self::SUCCESS;
    }

    /**
     * Les visiteurs des jours supprimés sont ajoutés au compteur archivé :
     * le compteur public du site ne baisse pas. La coupure se fait à minuit,
     * l'empreinte anonyme changeant chaque jour, aucun visiteur n'est compté deux fois.
     */
    private function prunePageViews($before): int
    {
        $old = PageView::where('created_at', '<', $before);
        $visitors = (clone $old)->distinct()->count('visitor_hash');

        if ($visitors === 0) {
            return 0;
        }

        $profile = ProfileSetting::query()->first();

        return DB::transaction(function () use ($old, $visitors, $profile) {
            $profile?->increment('archived_visitors', $visitors);
            $deleted = $old->delete();
            Cache::forget('analytics.public-visitors');

            return $deleted;
        });
    }
}
