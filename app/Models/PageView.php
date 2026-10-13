<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PageView extends Model
{
    protected $fillable = [
        'uuid', 'visitor_hash', 'session_hash', 'path', 'title', 'locale', 'referrer_host',
        'country_code', 'device', 'browser', 'os', 'duration_seconds',
    ];

    protected function casts(): array
    {
        return ['duration_seconds' => 'integer'];
    }

    /**
     * Nombre total de visiteurs affiché publiquement (visiteurs uniques par
     * jour, cumulés depuis le lancement). Mis en cache dix minutes.
     */
    public static function publicVisitorCount(): int
    {
        return (int) Cache::remember('analytics.public-visitors', now()->addMinutes(10), function () {
            // L'empreinte change chaque jour : un visiteur revenu trois jours compte trois fois.
            return static::query()->distinct()->count('visitor_hash');
        });
    }
}
