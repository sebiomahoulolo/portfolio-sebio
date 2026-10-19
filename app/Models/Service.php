<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedSlug;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasLocalizedSlug, HasTranslations;

    protected array $translatable = ['title', 'summary', 'description', 'features', 'price_label', 'seo_description'];

    protected $attributes = ['is_published' => true];

    protected $fillable = [
        'title', 'slug', 'slug_en', 'summary', 'description', 'features', 'price_label', 'icon',
        'is_published', 'sort_order', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Liste « ce qui est inclus » dans la langue demandée, avec repli sur le français.
     *
     * @return list<string>
     */
    public function featuresFor(?string $locale = null): array
    {
        $values = $this->features ?? [];
        $locale ??= app()->getLocale();

        return $values[$locale] ?? $values[config('portfolio.default_locale')] ?? [];
    }
}
