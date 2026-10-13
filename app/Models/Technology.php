<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Technology extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'frontend' => 'Frontend',
        'backend' => 'Backend',
        'tools' => 'Outils',
        'other' => 'Autre',
    ];

    protected $fillable = ['name', 'slug', 'category', 'level', 'icon', 'show_in_skills', 'sort_order'];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'show_in_skills' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }
}
