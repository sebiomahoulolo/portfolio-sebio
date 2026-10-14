<div class="flex flex-col gap-6">
    <x-admin.page-header title="Services" description="Affichés sur l'accueil et sur la page /services. L'ordre ci-dessous est celui du site.">
        <x-slot:actions>
            @can('create', App\Models\Service::class)
                <flux:button variant="primary" icon="plus" :href="route('admin.services.create')" wire:navigate>Nouveau service</flux:button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @if ($this->services->isEmpty())
        <x-admin.empty-state icon="sparkles" title="Aucun service" description="Décrivez ce que vous proposez à vos clients." />
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Service</flux:table.column>
                <flux:table.column>Langues</flux:table.column>
                <flux:table.column>Visible</flux:table.column>
                <flux:table.column>Ordre</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->services as $service)
                    <flux:table.row :key="$service->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-lg bg-paper text-copper-dark dark:bg-zinc-700"><i class="{{ $service->icon ?: 'fas fa-code' }}"></i></span>
                                <div>
                                    <div class="font-medium text-ink dark:text-paper">{{ $service->translate('title', 'fr') }}</div>
                                    <div class="max-w-md truncate text-xs text-zinc-500">{{ $service->translate('summary', 'fr') }}</div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @foreach (config('portfolio.locales') as $code => $name)
                                <flux:badge size="sm" :color="$service->hasTranslation($code) ? 'green' : 'zinc'" inset="top bottom">{{ strtoupper($code) }}</flux:badge>
                            @endforeach
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:switch :checked="$service->is_published" wire:click="togglePublished({{ $service->id }})" />
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-1">
                                <flux:button size="xs" variant="ghost" icon="arrow-up" wire:click="move({{ $service->id }}, 'up')" :disabled="$loop->first" aria-label="Monter" />
                                <flux:button size="xs" variant="ghost" icon="arrow-down" wire:click="move({{ $service->id }}, 'down')" :disabled="$loop->last" aria-label="Descendre" />
                            </div>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button size="sm" variant="ghost" icon="arrow-top-right-on-square" :href="lroute('services.show', ['slug' => $service->slug], 'fr')" target="_blank" aria-label="Voir sur le site" />
                            @can('update', $service)
                                <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.services.edit', $service)" wire:navigate aria-label="Modifier" />
                            @endcan
                            @can('delete', $service)
                                <flux:button size="sm" variant="ghost" icon="trash" data-confirm="Supprimer ce service ?" data-confirm-text="Cette action est définitive." data-confirm-button="Supprimer" x-on:click="$confirm($el, () => $wire.delete({{ $service->id }}))" aria-label="Supprimer" />
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
