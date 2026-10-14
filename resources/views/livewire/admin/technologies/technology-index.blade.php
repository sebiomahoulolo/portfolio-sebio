<div class="flex flex-col gap-6">
    <x-admin.page-header title="Technologies" description="Associées aux projets et affichées dans la section « Compétences » du site.">
        <x-slot:actions>
            @can('create', App\Models\Technology::class)
                <flux:button variant="primary" icon="plus" wire:click="create">Ajouter</flux:button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @forelse ($this->technologies as $category => $items)
        <flux:card class="flex flex-col gap-2">
            <flux:heading>{{ App\Models\Technology::CATEGORIES[$category] ?? $category }}</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nom</flux:table.column>
                    <flux:table.column>Niveau</flux:table.column>
                    <flux:table.column>Compétences</flux:table.column>
                    <flux:table.column>Projets</flux:table.column>
                    <flux:table.column>Ordre</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($items as $tech)
                        <flux:table.row :key="$tech->id">
                            <flux:table.cell class="font-medium text-ink dark:text-paper">{{ $tech->name }}</flux:table.cell>
                            <flux:table.cell class="w-48">
                                @if ($tech->level !== null)
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-24 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                            <div class="h-full rounded-full bg-copper" style="width: {{ $tech->level }}%"></div>
                                        </div>
                                        <span class="text-xs tabular-nums text-zinc-500">{{ $tech->level }} %</span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($tech->show_in_skills)
                                    <flux:badge size="sm" color="green" inset="top bottom">Affichée</flux:badge>
                                @else
                                    <flux:badge size="sm" inset="top bottom">Masquée</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="tabular-nums">{{ $tech->projects_count }}</flux:table.cell>
                            <flux:table.cell class="tabular-nums">{{ $tech->sort_order }}</flux:table.cell>
                            <flux:table.cell align="end">
                                @can('update', $tech)
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $tech->id }})" aria-label="Modifier" />
                                @endcan
                                @can('delete', $tech)
                                    <flux:button size="sm" variant="ghost" icon="trash" data-confirm="Supprimer {{ $tech->name }} ? {{ $tech->projects_count ? 'Elle sera retirée de '.$tech->projects_count.' projet(s).' : '' }}" data-confirm-text="Cette action est définitive." data-confirm-button="Supprimer"
                                        x-on:click="$confirm($el, () => $wire.delete({{ $tech->id }}))" aria-label="Supprimer" />
                                @endcan
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @empty
        <x-admin.empty-state icon="cpu-chip" title="Aucune technologie" description="Ajoutez les langages et outils que vous maîtrisez." />
    @endforelse

    <flux:modal name="technology-form" class="w-full max-w-lg">
        <form wire:submit="save" class="flex flex-col gap-5">
            <flux:heading size="lg">{{ $editingId ? 'Modifier la technologie' : 'Nouvelle technologie' }}</flux:heading>
            <flux:input wire:model="name" label="Nom" required />
            <flux:select wire:model="category" label="Catégorie">
                @foreach (App\Models\Technology::CATEGORIES as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="level" type="number" min="0" max="100" label="Niveau (%)" description="Vide : pas de barre de niveau." />
                <flux:input wire:model="sort_order" type="number" min="0" label="Ordre" />
            </div>
            <flux:input wire:model="icon" label="Icône Font Awesome" placeholder="fab fa-laravel" description="Voir fontawesome.com/icons (version gratuite)." />
            <flux:switch wire:model="show_in_skills" label="Afficher dans la section « Compétences »" align="left" />
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button variant="primary" type="submit">Enregistrer</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
