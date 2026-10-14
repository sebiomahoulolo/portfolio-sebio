<form wire:submit="save" class="flex flex-col gap-6">
    <x-admin.page-header :title="$projectId ? 'Modifier le projet' : 'Nouveau projet'"
        description="Un brouillon peut être enregistré incomplet. La publication exige un titre et un résumé.">
        <x-slot:actions>
            <flux:button variant="ghost" :href="route('admin.projects.index')" wire:navigate>Retour à la liste</flux:button>
            @if ($this->project?->isPubliclyVisible())
                <flux:button icon="arrow-top-right-on-square" :href="route('projects.show', $this->project)" target="_blank">Voir</flux:button>
            @endif
            <flux:button variant="primary" type="submit" icon="check">Enregistrer</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('project_created'))
        <flux:callout variant="success" icon="check-circle" heading="Projet créé. Vous pouvez maintenant ajouter des images à la galerie ou compléter l'étude de cas." />
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            {{-- 1. Informations générales --}}
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Informations générales</flux:heading>
                <x-admin.translated-field model="form.title" label="Titre" required />
                <flux:input wire:model.blur="form.slug" label="Adresse (slug)" placeholder="Générée automatiquement à partir du titre"
                    description="Utilisée dans l'URL : /projets/{{ $form['slug'] ?: '...' }}" />
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <flux:input wire:model="form.category" label="Catégorie" list="project-categories" placeholder="Plateforme web" />
                        <datalist id="project-categories">
                            @foreach ($this->categories as $c)<option value="{{ $c }}"></option>@endforeach
                        </datalist>
                    </div>
                    <flux:input wire:model="form.client" label="Client" />
                    <flux:input wire:model="form.period" label="Période" placeholder="2024 - 2025" />
                </div>
            </flux:card>

            {{-- 2. Description et étude de cas --}}
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Description et étude de cas</flux:heading>
                <x-admin.translated-field model="form.excerpt" label="Résumé" type="textarea" :rows="2"
                    description="Affiché sur la carte du projet. 20 à 300 caractères." />
                <x-admin.translated-field model="form.description" label="Présentation générale" type="textarea" :rows="6"
                    description="Contexte, problème, solution, résultats. Une ligne vide sépare les paragraphes ; « ## » en début de ligne crée un intertitre." />
                <x-admin.translated-field model="form.role" label="Mon rôle" type="textarea" :rows="2" />
            </flux:card>

            {{-- Étude de cas structurée --}}
            <flux:card class="flex flex-col gap-5">
                <div>
                    <flux:heading>Étude de cas</flux:heading>
                    <flux:text class="mt-1 text-sm">Ce qui convainc un client : le contexte, le problème, votre solution et des résultats mesurables. Chaque section vide est masquée sur le site.</flux:text>
                </div>
                <x-admin.translated-field model="form.context" label="Contexte" type="textarea" :rows="3" description="Le client, son activité, la situation de départ." />
                <x-admin.translated-field model="form.challenge" label="Problème" type="textarea" :rows="3" description="Ce qui ne fonctionnait pas ou ce qu'il fallait construire." />
                <x-admin.translated-field model="form.solution" label="Solution" type="textarea" :rows="5" description="Votre démarche et les choix techniques. Listes avec « - »." />
                <x-admin.translated-field model="form.results" label="Résultats" type="textarea" :rows="3" description="Ce qui a changé pour le client, avec des chiffres réels si possible." />

                <flux:separator />

                <div class="flex items-center justify-between">
                    <flux:label>Chiffres clés</flux:label>
                    <flux:button size="sm" icon="plus" wire:click="addMetric" :disabled="count($metrics) >= 6">Ajouter un chiffre</flux:button>
                </div>
                @forelse ($metrics as $i => $metric)
                    <div wire:key="metric-{{ $i }}" class="grid items-end gap-3 sm:grid-cols-[120px_1fr_1fr_auto]">
                        <flux:input wire:model="metrics.{{ $i }}.value" label="Valeur" placeholder="3 000+" />
                        @foreach (config('portfolio.locales') as $code => $name)
                            <flux:input wire:model="metrics.{{ $i }}.label.{{ $code }}" :label="'Libellé '.strtoupper($code)" :placeholder="$code === 'fr' ? 'candidats inscrits' : 'registered candidates'" />
                        @endforeach
                        <flux:button variant="ghost" icon="trash" wire:click="removeMetric({{ $i }})" aria-label="Retirer" />
                    </div>
                @empty
                    <flux:text class="text-sm">Aucun chiffre. Exemples : « 3 000+ utilisateurs », « -40 % de temps de traitement », « 99,9 % de disponibilité ».</flux:text>
                @endforelse
            </flux:card>

            {{-- 3. Technologies --}}
            <flux:card class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <flux:heading>Technologies</flux:heading>
                    <flux:link :href="route('admin.technologies.index')" wire:navigate class="text-sm">Gérer la liste</flux:link>
                </div>
                @foreach ($this->technologies as $category => $items)
                    <flux:checkbox.group wire:model="technologyIds" :label="App\Models\Technology::CATEGORIES[$category] ?? $category" class="flex flex-wrap gap-x-5 gap-y-2">
                        @foreach ($items as $tech)
                            <flux:checkbox :value="$tech->id" :label="$tech->name" />
                        @endforeach
                    </flux:checkbox.group>
                @endforeach
            </flux:card>

            {{-- 4. Images et galerie --}}
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Images</flux:heading>

                <div class="flex flex-col gap-3">
                    <flux:label>Image de couverture</flux:label>
                    <div class="flex flex-wrap items-start gap-4">
                        @if ($cover?->isPreviewable())
                            <img src="{{ $cover->temporaryUrl() }}" alt="" class="h-28 w-44 rounded-lg object-cover">
                        @elseif ($this->project?->coverUrl())
                            <div class="relative">
                                <img src="{{ $this->project->coverUrl() }}" alt="" class="h-28 w-44 rounded-lg object-cover">
                                <flux:button size="xs" variant="danger" icon="x-mark" class="absolute end-1 top-1" data-confirm="Supprimer l'image de couverture ?" data-confirm-text="Cette action est définitive." data-confirm-button="Supprimer" x-on:click="$confirm($el, () => $wire.removeCover())" aria-label="Supprimer" />
                            </div>
                        @endif
                        <div class="flex-1">
                            <flux:input type="file" wire:model="cover" accept="image/jpeg,image/png,image/webp" />
                            <flux:text class="mt-1 text-xs">JPG, PNG ou WebP, 4 Mo maximum. Format conseillé : 1600 × 1000 px.</flux:text>
                            <flux:error name="cover" />
                        </div>
                    </div>
                    <x-admin.translated-field model="form.cover_alt" label="Texte alternatif de la couverture" description="Décrit l'image pour les lecteurs d'écran et le référencement." />
                </div>

                <flux:separator />

                <div class="flex flex-col gap-3">
                    <flux:label>Galerie</flux:label>
                    @if ($this->project?->images->isNotEmpty())
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($this->project->images as $image)
                                <div wire:key="image-{{ $image->id }}" class="flex gap-3 rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                                    <img src="{{ $image->url() }}" alt="" class="size-20 shrink-0 rounded object-cover">
                                    <div class="flex min-w-0 flex-1 flex-col gap-2">
                                        <flux:input size="sm" placeholder="Texte alternatif" value="{{ $image->translate('alt', 'fr') }}"
                                            x-on:change="$wire.updateImageAlt({{ $image->id }}, 'fr', $event.target.value)" />
                                        <div class="flex gap-1">
                                            <flux:button size="xs" variant="ghost" icon="arrow-up" wire:click="moveImage({{ $image->id }}, 'up')" :disabled="$loop->first" aria-label="Monter" />
                                            <flux:button size="xs" variant="ghost" icon="arrow-down" wire:click="moveImage({{ $image->id }}, 'down')" :disabled="$loop->last" aria-label="Descendre" />
                                            <flux:spacer />
                                            <flux:button size="xs" variant="ghost" icon="trash" data-confirm="Supprimer cette image ?" data-confirm-text="Cette action est définitive." data-confirm-button="Supprimer" x-on:click="$confirm($el, () => $wire.removeImage({{ $image->id }}))" aria-label="Supprimer" />
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($gallery)
                        <div class="flex flex-wrap gap-2">
                            @foreach (array_filter($gallery, fn ($file) => $file->isPreviewable()) as $file)
                                <img src="{{ $file->temporaryUrl() }}" alt="" class="size-20 rounded object-cover opacity-80">
                            @endforeach
                        </div>
                        <flux:text class="text-xs">Ces images seront ajoutées à l'enregistrement.</flux:text>
                    @endif

                    <flux:input type="file" wire:model="gallery" multiple accept="image/jpeg,image/png,image/webp" />
                    <flux:error name="gallery" />
                    <flux:error name="gallery.*" />
                </div>
            </flux:card>

            {{-- 7. SEO --}}
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Référencement</flux:heading>
                <x-admin.translated-field model="form.seo_title" label="Titre SEO" description="70 caractères maximum. Par défaut : le titre du projet." />
                <x-admin.translated-field model="form.seo_description" label="Méta-description" type="textarea" :rows="2" description="160 caractères maximum. Par défaut : le résumé." />
                <flux:switch wire:model="form.is_indexable" label="Autoriser l'indexation par les moteurs de recherche" align="left" />
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            {{-- 6. Publication --}}
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Publication</flux:heading>
                <flux:select wire:model.live="form.status" label="Statut">
                    @foreach ($statuses as $s)
                        <flux:select.option :value="$s->value">{{ $s->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="form.published_at" type="datetime-local" label="Date de publication"
                    description="Laissez vide pour publier immédiatement. Une date future programme la publication." />
                <flux:switch wire:model="form.is_featured" label="Mettre en avant sur l'accueil" align="left" />
                <flux:input wire:model="form.sort_order" type="number" min="0" label="Ordre d'affichage" description="Les plus petits nombres apparaissent en premier." />
            </flux:card>

            {{-- 5. Liens --}}
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Liens</flux:heading>
                <flux:input wire:model.blur="form.demo_url" type="url" label="Site ou démonstration" placeholder="https://" />
                <flux:input wire:model.blur="form.repository_url" type="url" label="Dépôt de code" placeholder="https://github.com/..." />
            </flux:card>
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <flux:button variant="primary" type="submit" icon="check">Enregistrer</flux:button>
    </div>
</form>
