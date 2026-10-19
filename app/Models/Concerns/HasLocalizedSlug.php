<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Adresse traduite : « slug » pour le français, « slug_en » pour l'anglais.
 *
 * Sans slug anglais, la version anglaise reprend le slug français. Le slug
 * anglais est créé à partir du titre anglais au premier enregistrement, puis
 * ne change plus seul : une adresse publiée doit rester stable.
 */
trait HasLocalizedSlug
{
    public static function bootHasLocalizedSlug(): void
    {
        static::saving(function (self $model) {
            if (filled($model->slug_en)) {
                return;
            }

            $title = ($model->getAttribute('title') ?? [])['en'] ?? null;
            $slug = Str::slug((string) $title);

            if ($slug !== '' && $slug !== $model->slug) {
                $model->slug_en = $model->uniqueEnglishSlug($slug);
            }
        });
    }

    public function slugFor(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' && filled($this->slug_en) ? $this->slug_en : $this->slug;
    }

    /**
     * Paramètres de route de la page dans chaque langue (sélecteur de langue, hreflang, sitemap).
     *
     * @return array<string, array{slug: string}>
     */
    public function localizedRouteParameters(): array
    {
        return collect(array_keys(config('portfolio.locales')))
            ->mapWithKeys(fn ($locale) => [$locale => ['slug' => $this->slugFor($locale)]])
            ->all();
    }

    /**
     * Retrouve un contenu par l'adresse demandée. En anglais, le slug anglais
     * est prioritaire, puis le slug français (anciens liens, contenus sans traduction).
     */
    public static function findBySlugFor(Builder $query, string $slug, string $locale): ?self
    {
        if ($locale === 'en') {
            $found = (clone $query)->where('slug_en', $slug)->first();
            if ($found) {
                return $found;
            }
        }

        return $query->where('slug', $slug)->first();
    }

    private function uniqueEnglishSlug(string $slug): string
    {
        $candidate = $slug;
        $i = 2;

        while (static::withoutGlobalScopes()->where('slug_en', $candidate)->whereKeyNot($this->getKey())->exists()) {
            $candidate = $slug.'-'.$i++;
        }

        return $candidate;
    }
}
