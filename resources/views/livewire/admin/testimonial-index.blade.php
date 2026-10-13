<div class="flex flex-col gap-6">
    <x-admin.page-header title="Avis et recommandations" description="Recommandations LinkedIn et avis déposés par les visiteurs. Un avis déposé sur le site n'est publié qu'après votre validation.">
        <x-slot:actions>
            <flux:button variant="ghost" icon="arrow-top-right-on-square" :href="lroute('reviews.create', [], 'fr')" target="_blank">Formulaire public</flux:button>
            @can('create', App\Models\Testimonial::class)
                <flux:button variant="primary" icon="plus" wire:click="create">Ajouter</flux:button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @php $pending = $this->testimonials->filter->isPending()->count(); @endphp
    @if ($pending)
        <flux:callout variant="warning" icon="clock" :heading="$pending.' avis en attente de validation'" />
    @endif

    @if ($this->testimonials->isEmpty())
        <x-admin.empty-state icon="chat-bubble-left-right" title="Aucune recommandation" description="Ajoutez les recommandations reçues sur LinkedIn." />
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($this->testimonials as $testimonial)
                <flux:card wire:key="t-{{ $testimonial->id }}" class="flex flex-col gap-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            @if ($testimonial->avatarUrl())
                                <img src="{{ $testimonial->avatarUrl() }}" alt="" class="size-10 rounded-full object-cover">
                            @else
                                <span class="flex size-10 items-center justify-center rounded-full bg-copper text-sm font-bold text-white">{{ $testimonial->initials() }}</span>
                            @endif
                            <div>
                                <div class="font-medium text-ink dark:text-paper">{{ $testimonial->author_name }}</div>
                                <div class="text-xs text-zinc-500">{{ $testimonial->translate('author_title', 'fr') }}</div>
                            </div>
                        </div>
                        @if ($testimonial->isPending())
                            <flux:badge size="sm" color="orange" icon="clock">À valider</flux:badge>
                        @elseif ($testimonial->status === 'rejected')
                            <flux:badge size="sm" color="red">Refusé</flux:badge>
                        @else
                            <flux:badge size="sm" :color="$testimonial->is_published ? 'green' : 'zinc'">{{ $testimonial->is_published ? 'Visible' : 'Masqué' }}</flux:badge>
                        @endif
                    </div>
                    @if ($testimonial->rating)
                        <div class="flex gap-0.5 text-amber-500" aria-label="Note {{ $testimonial->rating }} sur 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <flux:icon.star variant="solid" @class(['size-4', 'text-zinc-300 dark:text-zinc-600' => $i > $testimonial->rating]) />
                            @endfor
                        </div>
                    @endif
                    <flux:text class="line-clamp-4">« {{ $testimonial->translate('quote', 'fr') }} »</flux:text>
                    @if ($testimonial->email)
                        <flux:text class="text-xs"><flux:icon.envelope variant="micro" class="inline" /> {{ $testimonial->email }} (privé)</flux:text>
                    @endif
                    @if ($testimonial->isPending())
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary" icon="check" wire:click="approve({{ $testimonial->id }})">Approuver et publier</flux:button>
                            <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="reject({{ $testimonial->id }})">Refuser</flux:button>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <flux:text class="text-xs">{{ App\Models\Testimonial::SOURCES[$testimonial->source] ?? $testimonial->source }}@if ($testimonial->given_at) · {{ $testimonial->given_at->format('d/m/Y') }}@endif</flux:text>
                        <div class="flex gap-1">
                            @can('update', $testimonial)
                                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $testimonial->id }})" aria-label="Modifier" />
                            @endcan
                            @can('delete', $testimonial)
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $testimonial->id }})" wire:confirm="Supprimer cette recommandation ?" aria-label="Supprimer" />
                            @endcan
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    <flux:modal name="testimonial-form" class="w-full max-w-2xl">
        <form wire:submit="save" class="flex flex-col gap-5">
            <flux:heading size="lg">{{ $editingId ? 'Modifier la recommandation' : 'Nouvelle recommandation' }}</flux:heading>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="form.author_name" label="Nom de l'auteur" required />
                <flux:select wire:model="form.source" label="Source">
                    @foreach (App\Models\Testimonial::SOURCES as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <x-admin.translated-field model="form.author_title" label="Fonction et entreprise" />
            <x-admin.translated-field model="form.quote" label="Recommandation" type="textarea" :rows="5" required />
            <div class="grid gap-4 sm:grid-cols-4">
                <flux:input wire:model="form.given_at" type="date" label="Date" />
                <flux:select wire:model="form.rating" label="Note">
                    <flux:select.option value="">Sans note</flux:select.option>
                    @for ($i = 5; $i >= 1; $i--)
                        <flux:select.option :value="$i">{{ $i }} / 5</flux:select.option>
                    @endfor
                </flux:select>
                <flux:input wire:model="form.sort_order" type="number" min="0" label="Ordre" />
                <div class="flex items-end pb-2"><flux:switch wire:model="form.is_published" label="Visible" align="left" /></div>
            </div>
            <flux:input wire:model="form.source_url" type="url" label="Lien vers la source (facultatif)" placeholder="https://www.linkedin.com/..." />
            <div>
                <flux:input type="file" wire:model="avatar" label="Photo de l'auteur (facultatif)" accept="image/jpeg,image/png,image/webp" />
                <flux:error name="avatar" />
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button variant="primary" type="submit">Enregistrer</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
