<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasTranslations;

    public const EMPLOYMENT_TYPES = [
        'full_time' => 'Temps plein',
        'part_time' => 'Temps partiel',
        'freelance' => 'Freelance',
        'internship' => 'Stage',
        'volunteer' => 'Bénévolat',
    ];

    protected array $translatable = ['title', 'description'];

    protected $fillable = [
        'title', 'company', 'location', 'employment_type', 'description',
        'started_at', 'ended_at', 'is_visible', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function isCurrent(): bool
    {
        return $this->ended_at === null;
    }
}
