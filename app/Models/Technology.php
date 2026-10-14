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

    /**
     * Niveaux de maîtrise, du plus fort au plus faible (libellés traduits sur le site).
     *
     * - expert : utilisé au quotidien, capable de former quelqu'un
     * - confirmed : projets livrés en autonomie
     * - notions : déjà utilisé, hors cœur de métier
     */
    public const PROFICIENCIES = [
        'expert' => 'Expert',
        'confirmed' => 'Confirmé',
        'notions' => 'Notions',
    ];

    /** Nombre minimum de projets pour afficher le compteur sur le site. */
    public const MIN_PROJECTS_SHOWN = 2;

    protected $fillable = ['name', 'slug', 'category', 'proficiency', 'icon', 'show_in_skills', 'sort_order'];

    protected function casts(): array
    {
        return [
            'show_in_skills' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function proficiencyLabel(): ?string
    {
        return $this->proficiency ? __(self::PROFICIENCIES[$this->proficiency] ?? $this->proficiency) : null;
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }
}
