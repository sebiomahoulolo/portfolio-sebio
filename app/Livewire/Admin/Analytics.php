<?php

namespace App\Livewire\Admin;

use App\Models\PageView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Locale;

#[Layout('components.layouts.admin')]
#[Title('Statistiques')]
class Analytics extends Component
{
    use WithPagination;

    public const PERIODS = [
        '1' => "Aujourd'hui",
        '7' => '7 derniers jours',
        '30' => '30 derniers jours',
        '90' => '3 derniers mois',
        '365' => '12 derniers mois',
    ];

    #[Url]
    public string $period = '30';

    public function mount(): void
    {
        Gate::authorize('view-analytics');

        if (! array_key_exists($this->period, self::PERIODS)) {
            $this->period = '30';
        }
    }

    public function updatedPeriod(): void
    {
        $this->resetPage();
        unset($this->summary, $this->series, $this->breakdowns);
    }

    private function start(): Carbon
    {
        return $this->period === '1' ? now()->startOfDay() : now()->subDays((int) $this->period - 1)->startOfDay();
    }

    private function base(): Builder
    {
        return PageView::query()->where('created_at', '>=', $this->start());
    }

    #[Computed]
    public function summary(): array
    {
        $views = (clone $this->base())->count();
        $visits = (clone $this->base())->distinct()->count('session_hash');
        $visitors = (clone $this->base())->distinct()->count('visitor_hash');
        $duration = (int) (clone $this->base())->sum('duration_seconds');

        // Visites d'une seule page (rebond).
        $bounces = DB::query()
            ->fromSub((clone $this->base())->select('session_hash')->groupBy('session_hash')->havingRaw('COUNT(*) = 1'), 's')
            ->count();

        return [
            'visitors' => $visitors,
            'visits' => $visits,
            'views' => $views,
            'avg_duration' => $visits ? intdiv($duration, $visits) : 0,
            'pages_per_visit' => $visits ? round($views / $visits, 1) : 0,
            'bounce_rate' => $visits ? (int) round($bounces / $visits * 100) : 0,
            'online' => PageView::where('created_at', '>=', now()->subMinutes(5))->distinct()->count('session_hash'),
            'total_visitors' => PageView::publicVisitorCount(),
        ];
    }

    /**
     * Visiteurs et pages vues par jour (par mois sur douze mois).
     */
    #[Computed]
    public function series(): Collection
    {
        $monthly = $this->period === '365';
        $format = $monthly ? '%Y-%m' : '%Y-%m-%d';

        $rows = (clone $this->base())
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as bucket, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors")
            ->groupBy('bucket')
            ->pluck('views', 'bucket')
            ->toBase();
        $visitors = (clone $this->base())
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as bucket, COUNT(DISTINCT visitor_hash) as visitors")
            ->groupBy('bucket')
            ->pluck('visitors', 'bucket');

        $points = collect();
        $cursor = $this->start()->copy();
        while ($cursor <= now()) {
            $key = $cursor->format($monthly ? 'Y-m' : 'Y-m-d');
            $points->push([
                'label' => $monthly ? $cursor->translatedFormat('M Y') : $cursor->translatedFormat('d M'),
                'views' => (int) ($rows[$key] ?? 0),
                'visitors' => (int) ($visitors[$key] ?? 0),
            ]);
            $monthly ? $cursor->addMonth()->startOfMonth() : $cursor->addDay();
        }

        return $points;
    }

    #[Computed]
    public function breakdowns(): array
    {
        $top = fn (string $column, string $metric = 'COUNT(*)') => (clone $this->base())
            ->whereNotNull($column)
            ->selectRaw("{$column} as label, {$metric} as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'pages' => (clone $this->base())
                ->selectRaw('path, MAX(title) as title, COUNT(*) as total, ROUND(AVG(NULLIF(duration_seconds, 0))) as avg_duration')
                ->groupBy('path')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            'countries' => $top('country_code', 'COUNT(DISTINCT visitor_hash)'),
            'referrers' => $top('referrer_host'),
            'devices' => $top('device', 'COUNT(DISTINCT visitor_hash)'),
            'browsers' => $top('browser', 'COUNT(DISTINCT visitor_hash)'),
            'locales' => $top('locale'),
        ];
    }

    /**
     * Nom lisible d'une page : « Accueil » plutôt que « / », le titre du projet
     * ou de l'article plutôt que son adresse. « (EN) » signale la version anglaise.
     */
    public static function pageLabel(string $path, ?string $title = null): string
    {
        $english = $path === '/en' || str_starts_with($path, '/en/');
        $local = $english ? (substr($path, 3) ?: '/') : $path;

        $names = [
            '/' => 'Accueil',
            '/projets' => 'Projets', '/projects' => 'Projets',
            '/services' => 'Services',
            '/blog' => 'Blog',
            '/donner-mon-avis' => 'Donner mon avis', '/leave-a-review' => 'Donner mon avis',
            '/mentions-legales' => 'Mentions légales', '/legal-notice' => 'Mentions légales',
            '/confidentialite' => 'Confidentialité', '/privacy' => 'Confidentialité',
        ];

        // Pages détail : titre de la page, sans le nom du site ajouté à la fin.
        $label = $names[rtrim($local, '/') ?: '/']
            ?? (filled($title) ? trim(preg_split('/\s+—\s+/u', $title)[0]) : $local);

        return $english ? $label.' (EN)' : $label;
    }

    /**
     * Dernières visites, une ligne par visite (même session) : pages dans l'ordre,
     * durée cumulée, origine et appareil de la première page.
     */
    private function recentVisits()
    {
        $visits = (clone $this->base())
            ->select('session_hash')
            ->selectRaw('MIN(created_at) as started_at, MAX(created_at) as ended_at, COUNT(*) as pages_count, SUM(duration_seconds) as total_duration')
            ->groupBy('session_hash')
            ->orderByDesc('ended_at')
            ->paginate(15);

        $views = PageView::whereIn('session_hash', $visits->pluck('session_hash'))
            ->where('created_at', '>=', $this->start())
            ->orderBy('created_at')->orderBy('id')
            ->get()
            ->groupBy('session_hash');

        $visits->getCollection()->transform(function ($visit) use ($views) {
            $pages = $views->get($visit->session_hash, collect());
            $first = $pages->first();

            return (object) [
                'key' => $visit->session_hash,
                'started_at' => Carbon::parse($visit->started_at),
                'ended_at' => Carbon::parse($visit->ended_at),
                // Une page rechargée plusieurs fois de suite n'apparaît qu'une fois (« Accueil ×4 »).
                'pages' => $pages->reduce(function (array $steps, $view) {
                    $last = array_key_last($steps);
                    if ($last !== null && $steps[$last]['path'] === $view->path) {
                        $steps[$last]['times']++;
                        $steps[$last]['duration'] += (int) $view->duration_seconds;
                    } else {
                        $steps[] = [
                            'label' => self::pageLabel($view->path, $view->title),
                            'path' => $view->path,
                            'duration' => (int) $view->duration_seconds,
                            'times' => 1,
                        ];
                    }

                    return $steps;
                }, []),
                'pages_count' => (int) $visit->pages_count,
                'duration' => (int) $visit->total_duration,
                'country' => $first?->country_code,
                'device' => $first?->device,
                'browser' => $first?->browser,
                'os' => $first?->os,
                'referrer' => $first?->referrer_host,
            ];
        });

        return $visits;
    }

    public static function countryName(?string $code): string
    {
        if (! $code) {
            return 'Inconnu';
        }

        return class_exists(Locale::class) ? (Locale::getDisplayRegion('-'.$code, 'fr') ?: $code) : $code;
    }

    public static function duration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.' s';
        }

        return intdiv($seconds, 60).' min '.str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT).' s';
    }

    public function render()
    {
        return view('livewire.admin.analytics', [
            'recent' => $this->recentVisits(),
            'periods' => self::PERIODS,
        ]);
    }
}
