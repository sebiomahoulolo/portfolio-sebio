<?php

namespace App\Livewire\Admin\Technologies;

use App\Models\Technology;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Technologies')]
class TechnologyIndex extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $category = 'backend';

    public ?int $level = null;

    public string $icon = '';

    public bool $show_in_skills = true;

    public int $sort_order = 0;

    public function mount(): void
    {
        $this->authorize('viewAny', Technology::class);
    }

    #[Computed]
    public function technologies()
    {
        return Technology::query()
            ->withCount('projects')
            ->orderByRaw("FIELD(category, 'frontend', 'backend', 'tools', 'other')")
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
    }

    public function create(): void
    {
        $this->authorize('create', Technology::class);
        $this->resetForm();
        Flux::modal('technology-form')->show();
    }

    public function edit(int $id): void
    {
        $technology = Technology::findOrFail($id);
        $this->authorize('update', $technology);

        $this->resetValidation();
        $this->editingId = $technology->id;
        $this->fill($technology->only('name', 'category', 'level', 'sort_order', 'show_in_skills'));
        $this->icon = (string) $technology->icon;

        Flux::modal('technology-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'category' => ['required', Rule::in(array_keys(Technology::CATEGORIES))],
            'level' => ['nullable', 'integer', 'between:0,100'],
            // Classes Font Awesome uniquement, ex. « fab fa-laravel ».
            'icon' => ['nullable', 'string', 'max:60', 'regex:/^(fa[bsr]?|fa-(solid|brands|regular))( fa-[a-z0-9-]+)+$/'],
            'show_in_skills' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ], [], ['name' => 'nom', 'level' => 'niveau', 'icon' => 'icône']);

        $slug = Str::slug($validated['name']);
        $duplicate = Technology::where('slug', $slug)->when($this->editingId, fn ($q) => $q->whereKeyNot($this->editingId))->exists();

        if ($duplicate) {
            $this->addError('name', 'Cette technologie existe déjà.');

            return;
        }

        $validated['icon'] = $validated['icon'] ?: null;

        if ($this->editingId) {
            $technology = Technology::findOrFail($this->editingId);
            $this->authorize('update', $technology);
            $technology->update($validated + ['slug' => $slug]);
            ActivityLogger::log('technology.updated', "Technologie modifiée : {$technology->name}", $technology);
        } else {
            $this->authorize('create', Technology::class);
            $technology = Technology::create($validated + ['slug' => $slug]);
            ActivityLogger::log('technology.created', "Technologie ajoutée : {$technology->name}", $technology);
        }

        Flux::modal('technology-form')->close();
        Flux::toast('Technologie enregistrée.', variant: 'success');
        unset($this->technologies);
    }

    public function delete(int $id): void
    {
        $technology = Technology::withCount('projects')->findOrFail($id);
        $this->authorize('delete', $technology);

        ActivityLogger::log('technology.deleted', "Technologie supprimée : {$technology->name}", null, ['projects' => $technology->projects_count]);
        $technology->delete();

        Flux::toast('Technologie supprimée.', variant: 'success');
        unset($this->technologies);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'name', 'level', 'icon');
        $this->category = 'backend';
        $this->show_in_skills = true;
        $this->sort_order = (int) Technology::max('sort_order') + 1;
    }

    public function render()
    {
        return view('livewire.admin.technologies.technology-index');
    }
}
