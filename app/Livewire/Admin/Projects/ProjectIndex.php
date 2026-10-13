<?php

namespace App\Livewire\Admin\Projects;

use App\Enums\PublicationStatus;
use App\Models\Project;
use App\Models\Technology;
use App\Support\ActivityLogger;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Projets')]
class ProjectIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $technology = '';

    #[Url(except: 'active')]
    public string $view = 'active'; // active | trash

    #[Url]
    public string $sortBy = 'sort_order';

    #[Url]
    public string $sortDirection = 'asc';

    /** @var list<int> */
    public array $selected = [];

    public ?int $confirmingDeletion = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Project::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'category', 'technology', 'view'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['sort_order', 'updated_at', 'published_at', 'views_count'], true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = $column === 'sort_order' ? 'asc' : 'desc';
        }
    }

    /**
     * Les filtres sont appliqués à la requête SQL avant la pagination :
     * les actions groupées portent donc sur les mêmes enregistrements que la liste.
     */
    private function query(): Builder
    {
        return Project::query()
            ->when($this->view === 'trash', fn (Builder $q) => $q->onlyTrashed())
            ->when($this->search !== '', fn (Builder $q) => $q->where(function (Builder $q) {
                $q->whereTranslationLike('title', $this->search)
                    ->orWhere('slug', 'like', '%'.$this->search.'%')
                    ->orWhere('client', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status !== '', fn (Builder $q) => $q->where('status', $this->status))
            ->when($this->category !== '', fn (Builder $q) => $q->where('category', $this->category))
            ->when($this->technology !== '', fn (Builder $q) => $q->whereHas(
                'technologies', fn (Builder $t) => $t->where('technologies.id', $this->technology)
            ));
    }

    #[Computed]
    public function projects()
    {
        return $this->query()
            ->with('technologies:id,name')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->orderBy('id')
            ->paginate(15);
    }

    #[Computed]
    public function categories()
    {
        return Project::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
    }

    #[Computed]
    public function technologies()
    {
        return Technology::query()->orderBy('name')->get(['id', 'name']);
    }

    public function bulk(string $action): void
    {
        $projects = $this->query()->whereKey($this->selected)->get();

        $done = 0;

        foreach ($projects as $project) {
            $ability = match ($action) {
                'publish', 'archive', 'draft' => 'publish',
                'delete' => 'delete',
                'restore' => 'restore',
                default => abort(400),
            };

            // Autorisation vérifiée sur chaque élément, pas seulement sur l'action.
            if (! auth()->user()->can($ability, $project)) {
                continue;
            }

            match ($action) {
                'publish' => $this->publish($project),
                'archive' => $project->update(['status' => PublicationStatus::Archived]),
                'draft' => $project->update(['status' => PublicationStatus::Draft]),
                'delete' => $project->delete(),
                'restore' => $project->restore(),
            };

            if ($action !== 'publish' || $project->status === PublicationStatus::Published) {
                $done++;
                ActivityLogger::log("project.{$action}", "Action groupée « {$action} » : ".$project->translate('title'), $project);
            }
        }

        $skipped = count($this->selected) - $done;
        $this->selected = [];
        unset($this->projects);

        Flux::toast(
            "{$done} projet(s) traité(s).".($skipped > 0 ? " {$skipped} ignoré(s) (droits insuffisants ou informations manquantes)." : ''),
            variant: $skipped > 0 ? 'warning' : 'success',
        );
    }

    private function publish(Project $project): void
    {
        if ($project->missingFieldsForPublication() !== []) {
            return;
        }

        $project->update([
            'status' => PublicationStatus::Published,
            'published_at' => $project->published_at ?? now(),
        ]);
    }

    public function restore(int $id): void
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $project);

        $project->restore();
        ActivityLogger::log('project.restored', 'Projet restauré : '.$project->translate('title'), $project);

        Flux::toast('Projet restauré (statut inchangé).', variant: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeletion = $id;
        Flux::modal('confirm-delete')->show();
    }

    public function delete(): void
    {
        $project = Project::withTrashed()->findOrFail($this->confirmingDeletion);

        if ($project->trashed()) {
            $this->authorize('forceDelete', $project);
            ActivityLogger::log('project.force_deleted', 'Projet supprimé définitivement : '.$project->translate('title'), null, ['id' => $project->id]);
            $project->images->each(fn ($image) => Storage::disk(config('portfolio.media_disk'))->delete($image->path));
            if ($project->cover_image) {
                Storage::disk(config('portfolio.media_disk'))->delete($project->cover_image);
            }
            $project->forceDelete();
            Flux::toast('Projet supprimé définitivement.', variant: 'success');
        } else {
            $this->authorize('delete', $project);
            $project->delete();
            ActivityLogger::log('project.deleted', 'Projet placé dans la corbeille : '.$project->translate('title'), $project);
            Flux::toast('Projet placé dans la corbeille.', variant: 'success');
        }

        $this->confirmingDeletion = null;
        Flux::modal('confirm-delete')->close();
    }

    public function render()
    {
        return view('livewire.admin.projects.project-index', [
            'statuses' => PublicationStatus::cases(),
        ]);
    }
}
