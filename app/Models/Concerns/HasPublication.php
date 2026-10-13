<?php

namespace App\Models\Concerns;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Règle de visibilité publique commune aux projets et aux articles : un
 * contenu n'est visible que s'il est publié et que sa date de publication
 * est passée. Les brouillons et les contenus programmés restent masqués.
 */
trait HasPublication
{
    public function initializeHasPublication(): void
    {
        $this->mergeCasts([
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PublicationStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->isPast()
            && ! $this->trashed();
    }

    public function isScheduled(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at?->isFuture();
    }
}
