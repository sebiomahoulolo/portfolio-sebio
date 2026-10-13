<div class="flex flex-col gap-6">
    <x-admin.page-header title="Projets" description="Les projets publiés alimentent la page d'accueil et la galerie /projets.">
        <x-slot:actions>
            @can('create', App\Models\Project::class)
                <flux:button variant="primary" icon="plus" :href="route('admin.projects.create')" wire:navigate>Nouveau projet</flux:button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="flex flex-wrap items-end gap-3">
        <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass" placeholder="Rechercher un titre, un client..." class="max-w-xs" clearable />

        <flux:select wire:model.live="status" class="max-w-44">
            <flux:select.option value="">Tous les statuts</flux:select.option>
            @foreach ($statuses as $s)
                <flux:select.option :value="$s->value">{{ $s->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="category" class="max-w-48">
            <flux:select.option value="">Toutes les catégories</flux:select.option>
            @foreach ($this->categories as $c)
                <flux:select.option :value="$c">{{ $c }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="technology" class="max-w-48">
            <flux:select.option value="">Toutes les technologies</flux:select.option>
            @foreach ($this->technologies as $t)
                <flux:select.option :value="$t->id">{{ $t->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:spacer />

        <flux:select wire:model.live="view" class="max-w-40">
            <flux:select.option value="active">Projets</flux:select.option>
            <flux:select.option value="trash">Corbeille</flux:select.option>
        </flux:select>
    </div>

    @if (count($selected) > 0)
        <div class="flex flex-wrap items-center gap-2 rounded-lg bg-paper px-4 py-2 dark:bg-zinc-700">
            <flux:text class="me-2 font-medium">{{ count($selected) }} sélectionné(s)</flux:text>
            @if ($view === 'trash')
                <flux:button size="sm" wire:click="bulk('restore')">Restaurer</flux:button>
            @else
                <flux:button size="sm" wire:click="bulk('publish')">Publier</flux:button>
                <flux:button size="sm" wire:click="bulk('draft')">Repasser en brouillon</flux:button>
                <flux:button size="sm" wire:click="bulk('archive')">Archiver</flux:button>
                <flux:button size="sm" variant="danger" wire:click="bulk('delete')" wire:confirm="Placer les projets sélectionnés dans la corbeille ?">Mettre à la corbeille</flux:button>
            @endif
            <flux:button size="sm" variant="ghost" wire:click="$set('selected', [])">Annuler</flux:button>
        </div>
    @endif

    @if ($this->projects->isEmpty())
        <x-admin.empty-state icon="rectangle-stack" title="Aucun projet" :description="$view === 'trash' ? 'La corbeille est vide.' : 'Aucun projet ne correspond à ces critères.'" />
    @else
        <flux:table :paginate="$this->projects">
            <flux:table.columns>
                <flux:table.column class="w-8"></flux:table.column>
                <flux:table.column>Projet</flux:table.column>
                <flux:table.column>Statut</flux:table.column>
                <flux:table.column>Technologies</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'sort_order'" :direction="$sortDirection" wire:click="sort('sort_order')">Ordre</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'views_count'" :direction="$sortDirection" wire:click="sort('views_count')">Vues</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection" wire:click="sort('updated_at')">Modifié</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->projects as $project)
                    <flux:table.row :key="$project->id">
                        <flux:table.cell>
                            <flux:checkbox wire:model.live="selected" :value="$project->id" />
                        </flux:table.cell>
                        <flux:table.cell class="max-w-72">
                            <div class="flex items-center gap-3">
                                @if ($project->coverUrl())
                                    <img src="{{ $project->coverUrl() }}" alt="" class="size-10 rounded object-cover">
                                @else
                                    <div class="flex size-10 items-center justify-center rounded bg-paper text-copper-dark dark:bg-zinc-700"><flux:icon.photo class="size-5" /></div>
                                @endif
                                <div class="min-w-0">
                                    <div class="truncate font-medium text-ink dark:text-paper">
                                        {{ $project->translate('title') }}
                                        @if ($project->is_featured)<flux:icon.star variant="micro" class="inline text-copper" />@endif
                                    </div>
                                    <div class="truncate text-xs text-zinc-500">
                                        /{{ $project->slug }}
                                        @unless ($project->hasTranslation('en')) · <span title="Traduction anglaise manquante">FR seulement</span>@endunless
                                    </div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <x-admin.status-badge :status="$project->status" />
                            @if ($project->isScheduled())
                                <div class="mt-1 text-xs text-zinc-500">le {{ $project->published_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="max-w-56 truncate text-sm text-zinc-500">
                            {{ $project->technologies->pluck('name')->join(', ') }}
                        </flux:table.cell>
                        <flux:table.cell class="tabular-nums">{{ $project->sort_order }}</flux:table.cell>
                        <flux:table.cell class="tabular-nums">{{ $project->views_count }}</flux:table.cell>
                        <flux:table.cell class="text-sm text-zinc-500">{{ $project->updated_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-1">
                                @if (! $project->trashed())
                                    @can('update', $project)
                                        <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.projects.edit', $project)" wire:navigate aria-label="Modifier" />
                                    @endcan
                                    @if ($project->isPubliclyVisible())
                                        <flux:button size="sm" variant="ghost" icon="arrow-top-right-on-square" :href="route('projects.show', $project)" target="_blank" aria-label="Voir sur le site" />
                                    @endif
                                    @can('delete', $project)
                                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $project->id }})" aria-label="Supprimer" />
                                    @endcan
                                @else
                                    @can('restore', $project)
                                        <flux:button size="sm" variant="ghost" icon="arrow-uturn-left" wire:click="restore({{ $project->id }})" aria-label="Restaurer" />
                                    @endcan
                                    @can('forceDelete', $project)
                                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $project->id }})" aria-label="Supprimer définitivement" />
                                    @endcan
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    <flux:modal name="confirm-delete" class="min-w-88">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">{{ $view === 'trash' ? 'Supprimer définitivement ?' : 'Mettre à la corbeille ?' }}</flux:heading>
                <flux:text class="mt-2">
                    {{ $view === 'trash'
                        ? 'Le projet et ses images seront supprimés. Cette action est irréversible.'
                        : 'Le projet disparaîtra du site. Vous pourrez le restaurer depuis la corbeille.' }}
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="delete">Confirmer</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
