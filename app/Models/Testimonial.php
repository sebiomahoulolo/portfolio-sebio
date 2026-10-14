<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasTranslations;

    public const SOURCES = [
        'linkedin' => 'Recommandation LinkedIn',
        'client' => 'Client',
        'site' => 'Avis déposé sur le site',
        'other' => 'Autre',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'rejected' => 'Refusé',
    ];

    protected array $translatable = ['author_title', 'quote'];

    protected $attributes = ['is_published' => true, 'source' => 'linkedin', 'status' => 'approved'];

    protected $fillable = [
        'author_name', 'author_title', 'quote', 'source', 'source_url', 'avatar_path',
        'email', 'rating', 'given_at', 'is_published', 'sort_order',
    ];

    protected $hidden = ['email', 'ip_hash'];

    protected function casts(): array
    {
        return [
            'given_at' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'rating' => 'integer',
        ];
    }

    /**
     * Visible sur le site : approuvé par l'administrateur et non masqué.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('status', 'approved');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? media_url($this->avatar_path) : null;
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->author_name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
    }
}
