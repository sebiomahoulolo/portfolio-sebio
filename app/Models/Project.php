<?php

namespace App\Models;

use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasPublication, HasTranslations, SoftDeletes;

    protected array $translatable = [
        'title', 'excerpt', 'description', 'context', 'challenge', 'solution', 'results',
        'role', 'cover_alt', 'seo_title', 'seo_description',
    ];

    /** Sections de l'étude de cas, dans l'ordre d'affichage. */
    public const CASE_STUDY_SECTIONS = [
        'context' => 'Contexte',
        'challenge' => 'Problème',
        'solution' => 'Solution',
        'results' => 'Résultats',
    ];

    protected $attributes = ['status' => 'draft'];

    protected $fillable = [
        'title', 'slug', 'excerpt', 'description', 'context', 'challenge', 'solution', 'results', 'metrics',
        'role', 'category', 'client', 'period',
        'status', 'is_featured', 'sort_order', 'cover_image', 'cover_alt',
        'demo_url', 'repository_url', 'seo_title', 'seo_description', 'is_indexable', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_indexable' => 'boolean',
            'sort_order' => 'integer',
            'views_count' => 'integer',
            'metrics' => 'array',
        ];
    }

    public function hasCaseStudy(): bool
    {
        foreach (array_keys(self::CASE_STUDY_SECTIONS) as $section) {
            if (filled($this->translate($section))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Chiffres clés dans la langue courante : [['value' => '3 000+', 'label' => '...'], ...]
     *
     * @return list<array{value: string, label: string}>
     */
    public function metricsFor(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return collect($this->metrics ?? [])
            ->filter(fn ($m) => filled($m['value'] ?? null))
            ->map(fn ($m) => [
                'value' => $m['value'],
                'label' => $m['label'][$locale] ?? $m['label'][config('portfolio.default_locale')] ?? '',
            ])
            ->values()
            ->all();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)
            ->withTimestamps()
            ->orderBy('sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image
            ? media_url($this->cover_image)
            : null;
    }

    /**
     * Un projet ne peut être publié que si ses informations essentielles
     * sont renseignées dans la langue par défaut.
     *
     * @return list<string> champs manquants
     */
    public function missingFieldsForPublication(): array
    {
        $missing = [];

        if (blank($this->translate('title', 'fr'))) {
            $missing[] = 'titre';
        }
        if (blank($this->slug)) {
            $missing[] = 'slug';
        }
        if (mb_strlen((string) $this->translate('excerpt', 'fr')) < 20) {
            $missing[] = 'résumé (20 caractères minimum)';
        }

        return $missing;
    }
}
