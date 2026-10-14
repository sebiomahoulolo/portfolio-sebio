<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Profil public : une seule ligne, récupérée avec ProfileSetting::current().
 */
class ProfileSetting extends Model
{
    use HasTranslations {
        translate as private translateRaw;
    }

    /** Marqueur à écrire dans les textes du profil : remplacé par les années d'expérience. */
    public const YEARS_PLACEHOLDER = '{ans}';

    private ?Carbon $careerStart = null;

    private bool $careerStartLoaded = false;

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

    /**
     * Langues parlées dans la langue demandée : [['name' => ..., 'level' => ...], ...].
     *
     * Format actuel : { "fr": [...], "en": [...] }. L'ancien format (une seule
     * liste en français) reste lu : il est alors traduit avec les fichiers de langue.
     *
     * @return list<array{name: string, level: string}>
     */
    public function languagesFor(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $values = $this->spoken_languages ?? [];
        $default = config('portfolio.default_locale');

        if (array_is_list($values)) {
            return $this->translateLanguages($values, $locale);
        }

        if (! empty($values[$locale])) {
            return $values[$locale];
        }

        // Pas encore de version dans cette langue : la version française, traduite si possible.
        return $this->translateLanguages($values[$default] ?? [], $locale);
    }

    private function translateLanguages(array $languages, string $locale): array
    {
        return array_map(fn ($language) => [
            'name' => __($language['name'] ?? '', [], $locale),
            'level' => __($language['level'] ?? '', [], $locale),
        ], $languages);
    }

    /**
     * Textes du profil dans la langue demandée, avec {ans} remplacé par le
     * nombre d'années d'expérience calculé : l'accroche reste juste chaque année.
     */
    public function translate(string $field, ?string $locale = null): ?string
    {
        $value = $this->translateRaw($field, $locale);

        if ($value === null || ! str_contains($value, self::YEARS_PLACEHOLDER)) {
            return $value;
        }

        return str_replace(self::YEARS_PLACEHOLDER, (string) ($this->experienceYears() ?? ''), $value);
    }

    /**
     * Remplace une durée écrite en dur (« plus de 3 ans », « over 3 years »)
     * par le marqueur {ans} : le texte saisi dans l'administration reste juste
     * les années suivantes, sans y penser.
     */
    public static function withYearsPlaceholder(string $text): string
    {
        return preg_replace([
            '/\b(plus de|depuis plus de|depuis près de|près de|depuis) \d+\+? ans\b/u',
            '/\b\d+\+? ans d([’\']) ?expérience/u',
            '/\b(over|more than|for over|for more than|nearly|almost) \d+\+? years\b/u',
            '/\b\d+\+? years of experience\b/u',
        ], [
            '$1 '.self::YEARS_PLACEHOLDER.' ans',
            self::YEARS_PLACEHOLDER.' ans d$1expérience',
            '$1 '.self::YEARS_PLACEHOLDER.' years',
            self::YEARS_PLACEHOLDER.' years of experience',
        ], $text) ?? $text;
    }

    /**
     * Début de carrière : le premier poste visible du parcours.
     */
    public function careerStart(): ?Carbon
    {
        if (! $this->careerStartLoaded) {
            $first = Experience::visible()->min('started_at');
            $this->careerStart = $first ? Carbon::parse($first) : null;
            $this->careerStartLoaded = true;
        }

        return $this->careerStart;
    }

    /**
     * Années révolues depuis le premier poste ; la valeur saisie dans le profil
     * ne sert que si le parcours est vide.
     */
    public function experienceYears(): ?int
    {
        if ($start = $this->careerStart()) {
            return max(1, (int) floor($start->diffInYears(now())));
        }

        return $this->years_experience ?: null;
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

    /**
     * Photo de profil : celle téléversée dans l'administration, sinon le portrait fourni avec le site.
     */
    public function photoUrl(): string
    {
        return $this->avatar_path
            ? $this->mediaUrl($this->avatar_path)
            : asset('images/profile/mahoulolo-sebio.jpg');
    }

    public function mediaUrl(?string $path): ?string
    {
        return media_url($path);
    }
}
