<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    use HasTranslations;

    public const SOURCES = [
        'linkedin' => 'Recommandation LinkedIn',
        'client' => 'Client',
        'other' => 'Autre',
    ];

    protected array $translatable = ['author_title', 'quote'];

    protected $attributes = ['is_published' => true, 'source' => 'linkedin'];

    protected $fillable = [
        'author_name', 'author_title', 'quote', 'source', 'source_url', 'avatar_path',
        'given_at', 'is_published', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'given_at' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk(config('portfolio.media_disk'))->url($this->avatar_path) : null;
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->author_name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
    }
}
