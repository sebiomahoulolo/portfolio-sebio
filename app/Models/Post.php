<?php

namespace App\Models;

use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory, HasPublication, HasTranslations, SoftDeletes;

    protected array $translatable = ['title', 'excerpt', 'body', 'seo_title', 'seo_description'];

    protected $attributes = ['status' => 'draft'];

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'tags', 'cover_image', 'status',
        'seo_title', 'seo_description', 'is_indexable', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_indexable' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? Storage::disk(config('portfolio.media_disk'))->url($this->cover_image) : null;
    }

    /**
     * Temps de lecture estimé en minutes (230 mots par minute).
     */
    public function readingMinutes(?string $locale = null): int
    {
        $words = str_word_count(strip_tags((string) $this->translate('body', $locale)));

        return max(1, (int) ceil($words / 230));
    }

    /**
     * @return list<string> champs manquants pour publier
     */
    public function missingFieldsForPublication(): array
    {
        $missing = [];

        if (blank($this->translate('title', 'fr'))) {
            $missing[] = 'titre';
        }
        if (mb_strlen((string) $this->translate('body', 'fr')) < 200) {
            $missing[] = 'contenu (200 caractères minimum)';
        }

        return $missing;
    }
}
