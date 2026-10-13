<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Profil public : une seule ligne, récupérée avec ProfileSetting::current().
 */
class ProfileSetting extends Model
{
    use HasTranslations;

    protected array $translatable = [
        'headline', 'subtitle', 'tagline', 'expertise', 'intro', 'about', 'objective', 'current_position',
        'featured_project', 'interests', 'seo_title', 'seo_description',
    ];

    protected $fillable = [
        'name', 'headline', 'subtitle', 'tagline', 'expertise', 'years_experience',
        'intro', 'about', 'objective', 'current_position',
        'featured_project', 'featured_project_url', 'email', 'phone', 'whatsapp', 'location',
        'avatar_path', 'cv_path', 'social_links', 'spoken_languages', 'interests',
        'is_available', 'seo_title', 'seo_description', 'og_image_path',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'spoken_languages' => 'array',
            'is_available' => 'boolean',
            'years_experience' => 'integer',
        ];
    }

    /**
     * Badges d'expertise dans la langue demandée.
     *
     * @return list<string>
     */
    public function expertiseFor(?string $locale = null): array
    {
        return $this->listFor('expertise', $locale);
    }

    /**
     * @return list<string>
     */
    private function listFor(string $field, ?string $locale): array
    {
        $values = $this->getAttribute($field) ?? [];
        $locale ??= app()->getLocale();

        return $values[$locale] ?? $values[config('portfolio.default_locale')] ?? [];
    }

    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'name' => config('app.name'),
            'headline' => ['fr' => ''],
        ]);
    }

    /**
     * Centres d'intérêt dans la langue demandée (liste), avec repli sur le français.
     *
     * @return list<string>
     */
    public function interestsFor(?string $locale = null): array
    {
        return $this->listFor('interests', $locale);
    }

    public function social(string $network): ?string
    {
        return ($this->social_links ?? [])[$network] ?? null;
    }

    public function mediaUrl(?string $path): ?string
    {
        return $path ? Storage::disk(config('portfolio.media_disk'))->url($path) : null;
    }
}
