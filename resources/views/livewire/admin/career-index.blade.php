@php
    $period = fn ($date) => $date ? Str::ucfirst($date->translatedFormat('M Y')) : null;
@endphp

<div class="flex flex-col gap-8">
    <x-admin.page-header title="Parcours" description="Expériences, formations et certifications affichées dans la section « Parcours » du site." />

    {{-- Expériences --}}
    <flux:card class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">Expériences</flux:heading>
            <flux:button size="sm" icon="plus" wire:click="create('experience')">Ajouter</flux:button>
        </div>
        @forelse ($this->experiences as $item)
            <div wire:key="exp-{{ $item->id }}" class="flex items-start justify-between gap-4 border-t border-zinc-100 pt-4 first-of-type:border-0 dark:border-zinc-700">
                <div>
                    <div class="font-medium text-ink dark:text-paper">{{ $item->translate('title', 'fr') }} @unless ($item->is_visible)<flux:badge size="sm">Masquée</flux:badge>@endunless</div>
                    <div class="text-sm text-zinc-500">{{ $item->company }} · {{ $period($item->started_at) }} — {{ $item->isCurrent() ? "aujourd'hui" : $period($item->ended_at) }}</div>
                </div>
                <div class="flex shrink-0 gap-1">
                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit('experience', {{ $item->id }})" aria-label="Modifier" />
                    @can('delete', $item)
                        <flux:button size="sm" variant="ghost" icon="trash" data-confirm="Supprimer cette expérience ?" data-confirm-text="Cette action est définitive." data-confirm-button="Supprimer" x-on:click="$confirm($el, () => $wire.delete('experience', {{ $item->id }}))" aria-label="Supprimer" />
                    @endcan
                </div>
            </div>
        @empty
            <flux:text>Aucune expérience.</flux:text>
        @endforelse
    </flux:card>

    <div class="grid gap-8 lg:grid-cols-2">
        {{-- Formations --}}
        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Formations</flux:heading>
                <flux:button size="sm" icon="plus" wire:click="create('education')">Ajouter</flux:button>
            </div>
            @forelse ($this->educations as $item)
                <div wire:key="edu-{{ $item->id }}" class="flex items-start justify-between gap-4 border-t border-zinc-100 pt-4 first-of-type:border-0 dark:border-zinc-700">
                    <div>
                        <div class="font-medium text-ink dark:text-paper">{{ $item->translate('degree', 'fr') }}</div>
                        <div class="text-sm text-zinc-500">{{ $item->institution }} · {{ collect([$item->start_year, $item->end_year])->filter()->join(' — ') }}</div>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit('education', {{ $item->id }})" aria-label="Modifier" />
                        @can('delete', $item)
                            <flux:button size="sm" variant="ghost" icon="trash" data-confirm="Supprimer cette formation ?" data-confirm-text="Cette action est définitive." data-confirm-button="Supprimer" x-on:click="$confirm($el, () => $wire.delete('education', {{ $item->id }}))" aria-label="Supprimer" />
                        @endcan
                    </div>
                </div>
            @empty
                <flux:text>Aucune formation.</flux:text>
            @endforelse
        </flux:card>

        {{-- Certifications --}}
        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Certifications</flux:heading>
                <flux:button size="sm" icon="plus" wire:click="create('certification')">Ajouter</flux:button>
            </div>
            @forelse ($this->certifications as $item)
                <div wire:key="cert-{{ $item->id }}" class="flex items-start justify-between gap-4 border-t border-zinc-100 pt-4 first-of-type:border-0 dark:border-zinc-700">
                    <div>
                        <div class="font-medium text-ink dark:text-paper">{{ $item->translate('name', 'fr') }}</div>
                        <div class="text-sm text-zinc-500">{{ $item->issuer }}@if ($item->issued_at) · {{ $period($item->issued_at) }}@endif</div>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit('certification', {{ $item->id }})" aria-label="Modifier" />
                        @can('delete', $item)
                            <flux:button size="sm" variant="ghost" icon="trash" data-confirm="Supprimer cette certification ?" data-confirm-text="Cette action est définitive." data-confirm-button="Supprimer" x-on:click="$confirm($el, () => $wire.delete('certification', {{ $item->id }}))" aria-label="Supprimer" />
                        @endcan
                    </div>
                </div>
            @empty
                <flux:text>Aucune certification.</flux:text>
            @endforelse
        </flux:card>
    </div>

    <flux:modal name="career-form" class="w-full max-w-2xl">
        <form wire:submit="save" class="flex flex-col gap-5">
            <flux:heading size="lg">{{ $editingId ? 'Modifier' : 'Ajouter' }} : {{ $labels[$kind] }}</flux:heading>

            @if ($kind === 'experience')
                <x-admin.translated-field model="form.title" label="Intitulé du poste" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="form.company" label="Entreprise" required />
                    <flux:input wire:model="form.location" label="Lieu" />
                </div>
                <flux:select wire:model="form.employment_type" label="Type de contrat">
                    @foreach (App\Models\Experience::EMPLOYMENT_TYPES as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="form.started_at" type="date" label="Début" required />
                    <flux:input wire:model="form.ended_at" type="date" label="Fin" x-bind:disabled="$wire.form.is_current" />
                </div>
                <flux:switch wire:model.live="form.is_current" label="Poste actuel" align="left" />
                <x-admin.translated-field model="form.description" label="Description" type="textarea" :rows="3" />
            @elseif ($kind === 'education')
                <x-admin.translated-field model="form.degree" label="Diplôme" required />
                <x-admin.translated-field model="form.field" label="Spécialité" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="form.institution" label="Établissement" required />
                    <flux:input wire:model="form.location" label="Lieu" />
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="form.start_year" type="number" label="Année de début" />
                    <flux:input wire:model="form.end_year" type="number" label="Année de fin" />
                    <flux:input wire:model="form.sort_order" type="number" min="0" label="Ordre" />
                </div>
            @else
                <x-admin.translated-field model="form.name" label="Nom de la certification" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="form.issuer" label="Organisme" required />
                    <flux:input wire:model="form.issued_at" type="date" label="Date d'obtention" />
                </div>
                <flux:input wire:model="form.credential_url" type="url" label="Lien de vérification (facultatif)" placeholder="https://" />
            @endif

            <flux:switch wire:model="form.is_visible" label="Visible sur le site" align="left" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button variant="primary" type="submit">Enregistrer</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
