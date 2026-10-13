<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    use HasTranslations;

    protected array $translatable = ['name'];

    protected $fillable = ['name', 'issuer', 'issued_at', 'credential_url', 'is_visible', 'sort_order'];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}
