<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Contenus bilingues stockés en JSON ({"fr": "...", "en": "..."}).
 *
 * Le modèle déclare ses champs traduits dans $translatable. La lecture
 * passe par translate() : si la traduction demandée manque, la version
 * dans la langue par défaut est renvoyée, et hasTranslation() permet à la
 * vue d'afficher un indicateur plutôt que de présenter la page comme traduite.
 */
trait HasTranslations
{
    public function initializeHasTranslations(): void
    {
        $this->mergeCasts(array_fill_keys($this->translatable, 'array'));
    }

    public function translate(string $field, ?string $locale = null): ?string
    {
        $values = $this->getAttribute($field) ?? [];
        $locale ??= app()->getLocale();
        $default = config('portfolio.default_locale', 'fr');

        $value = $values[$locale] ?? null;

        if (blank($value)) {
            $value = $values[$default] ?? null;
        }

        // Dernier recours : n'importe quelle langue renseignée (ex. un avis
        // déposé en anglais affiché sur la version française).
        if (blank($value)) {
            $value = collect($values)->first(fn ($v) => filled($v) && is_string($v));
        }

        return blank($value) || ! is_string($value) ? null : $value;
    }

    public function hasTranslation(string $locale, ?string $field = null): bool
    {
        $fields = $field ? [$field] : [$this->translatable[0]];

        foreach ($fields as $name) {
            if (blank(($this->getAttribute($name) ?? [])[$locale] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Recherche dans un champ traduit, toutes langues confondues.
     */
    public function scopeWhereTranslationLike(Builder $query, string $field, string $term): Builder
    {
        $locales = array_keys(config('portfolio.locales'));

        return $query->where(function (Builder $q) use ($field, $term, $locales) {
            foreach ($locales as $locale) {
                $q->orWhere("{$field}->{$locale}", 'like', '%'.$term.'%');
            }
        });
    }
}
