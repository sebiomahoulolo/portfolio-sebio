<form wire:submit="save" class="flex flex-col gap-6">
    <x-admin.page-header :title="$postId ? 'Modifier l’article' : 'Nouvel article'"
        description="Un brouillon peut rester incomplet. La publication exige un titre et un contenu de 200 caractères minimum.">
        <x-slot:actions>
            <flux:button variant="ghost" :href="route('admin.posts.index')" wire:navigate>Retour à la liste</flux:button>
            @if ($post?->isPubliclyVisible())
                <flux:button icon="arrow-top-right-on-square" :href="lroute('blog.show', ['slug' => $post->slug], 'fr')" target="_blank">Voir</flux:button>
            @endif
            <flux:button variant="primary" type="submit" icon="check">Enregistrer</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <flux:card class="flex flex-col gap-5">
                <x-admin.translated-field model="form.title" label="Titre" required />
                <x-admin.translated-field model="form.excerpt" label="Chapeau" type="textarea" :rows="2" description="Résumé affiché sur la liste des articles (300 caractères maximum)." />
                <x-admin.translated-field model="form.body" label="Contenu" type="textarea" :rows="22"
                    description="« ## » intertitre, « ### » sous-titre, « - » liste, « > » citation, ``` bloc de code ```, `code` dans le texte, **gras**." />
            </flux:card>

            <flux:card class="flex flex-col gap-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="photo" class="size-5 text-copper-dark" />
                    <flux:heading>Images et vidéos</flux:heading>
                </div>
                <flux:input type="file" wire:model="media" multiple accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm" />
                <div wire:loading wire:target="media" class="text-sm text-zinc-500">Envoi en cours…</div>
                <flux:error name="media" />
                <flux:error name="media.*" />
                <flux:text class="text-xs leading-relaxed">
                    Le fichier est ajouté à la fin du contenu sous la forme <code>![](adresse)</code> : déplacez cette ligne là où il doit apparaître et écrivez une légende entre les crochets, par exemple <code>![Tableau de bord](adresse)</code>.
                    Images JPG, PNG, WebP, GIF ; vidéos MP4 ou WebM (50 Mo maximum).
                    Pour une vidéo YouTube ou Vimeo, collez simplement son lien seul sur une ligne.
                </flux:text>
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <flux:heading>Référencement</flux:heading>
                <x-admin.translated-field model="form.seo_title" label="Titre SEO" description="70 caractères maximum. Par défaut : le titre." />
                <x-admin.translated-field model="form.seo_description" label="Méta-description" type="textarea" :rows="2" description="160 caractères maximum. Par défaut : le chapeau." />
                <flux:switch wire:model="form.is_indexable" label="Autoriser l'indexation" align="left" />
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Publication</flux:heading>
                <flux:select wire:model.live="form.status" label="Statut">
                    @foreach ($statuses as $s)
                        <flux:select.option :value="$s->value">{{ $s->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="form.published_at" type="datetime-local" label="Date de publication" description="Vide : maintenant. Une date future programme l'article." />
                <flux:input wire:model.blur="form.slug" label="Adresse (slug)" placeholder="Générée à partir du titre" description="/blog/{{ $form['slug'] ?: '...' }}" />
                <flux:input wire:model="tags" label="Thèmes" placeholder="Laravel, Sécurité, SaaS" description="Séparés par des virgules." />
            </flux:card>

            <flux:card class="flex flex-col gap-4">
                <flux:heading>Image de couverture</flux:heading>
                @if ($cover?->isPreviewable())
                    <img src="{{ $cover->temporaryUrl() }}" alt="" class="aspect-video w-full rounded-lg object-cover">
                @elseif ($post?->coverUrl())
                    <img src="{{ $post->coverUrl() }}" alt="" class="aspect-video w-full rounded-lg object-cover">
                @endif
                <flux:input type="file" wire:model="cover" accept="image/jpeg,image/png,image/webp" />
                <flux:error name="cover" />
                <flux:text class="text-xs">Facultative. 1600 × 800 px conseillé.</flux:text>
            </flux:card>
        </div>
    </div>
</form>
