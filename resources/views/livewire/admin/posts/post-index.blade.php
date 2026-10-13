<div class="flex flex-col gap-6">
    <x-admin.page-header title="Blog" description="Articles techniques publiés sur /blog, en français et en anglais.">
        <x-slot:actions>
            @can('create', App\Models\Post::class)
                <flux:button variant="primary" icon="plus" :href="route('admin.posts.create')" wire:navigate>Nouvel article</flux:button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass" placeholder="Rechercher un titre..." class="max-w-xs" clearable />
        <flux:select wire:model.live="status" class="max-w-44">
            <flux:select.option value="">Tous les statuts</flux:select.option>
            @foreach ($statuses as $s)
                <flux:select.option :value="$s->value">{{ $s->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->posts->isEmpty())
        <x-admin.empty-state icon="newspaper" title="Aucun article" description="Partagez votre expertise : Laravel, sécurité, SaaS, formation." />
    @else
        <flux:table :paginate="$this->posts">
            <flux:table.columns>
                <flux:table.column>Article</flux:table.column>
                <flux:table.column>Statut</flux:table.column>
                <flux:table.column>Langues</flux:table.column>
                <flux:table.column align="end">Lectures</flux:table.column>
                <flux:table.column>Modifié</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->posts as $post)
                    <flux:table.row :key="$post->id">
                        <flux:table.cell class="max-w-md">
                            <div class="truncate font-medium text-ink dark:text-paper">{{ $post->translate('title', 'fr') }}</div>
                            <div class="truncate text-xs text-zinc-500">/blog/{{ $post->slug }} · {{ $post->readingMinutes('fr') }} min</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <x-admin.status-badge :status="$post->status" />
                            @if ($post->isScheduled())<div class="mt-1 text-xs text-zinc-500">le {{ $post->published_at->format('d/m/Y H:i') }}</div>@endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @foreach (config('portfolio.locales') as $code => $name)
                                <flux:badge size="sm" :color="$post->hasTranslation($code, 'body') ? 'green' : 'zinc'" inset="top bottom">{{ strtoupper($code) }}</flux:badge>
                            @endforeach
                        </flux:table.cell>
                        <flux:table.cell align="end" class="tabular-nums">{{ $post->views_count }}</flux:table.cell>
                        <flux:table.cell class="text-sm text-zinc-500">{{ $post->updated_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($post->isPubliclyVisible())
                                <flux:button size="sm" variant="ghost" icon="arrow-top-right-on-square" :href="lroute('blog.show', ['slug' => $post->slug], 'fr')" target="_blank" aria-label="Voir" />
                            @endif
                            @can('update', $post)
                                <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.posts.edit', $post)" wire:navigate aria-label="Modifier" />
                            @endcan
                            @can('delete', $post)
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $post->id }})" wire:confirm="Placer cet article dans la corbeille ?" aria-label="Supprimer" />
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
