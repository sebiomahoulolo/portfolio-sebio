<form wire:submit="save" class="flex flex-col gap-6">
    <x-admin.page-header title="Profil public" description="Les textes et coordonnées affichés sur la page d'accueil.">
        <x-slot:actions>
            <flux:button variant="primary" type="submit" icon="check">Enregistrer</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Présentation</flux:heading>
                <flux:input wire:model="form.name" label="Nom affiché" required />
                <x-admin.translated-field model="form.headline" label="Titre professionnel" required />
                <x-admin.translated-field model="form.subtitle" label="Sous-titre" description="Ex. « Fondateur & CEO d'AfriCode »." />
                <x-admin.translated-field model="form.tagline" label="Accroche principale"
                    description="Grand titre de l'accueil. Deux phrases : la seconde s'affiche en couleur (ex. « Je bâtis des SaaS sécurisés. Je forme les devs de demain. »)." />
                <x-admin.translated-field model="expertise" label="Badges d'expertise" description="Séparés par des virgules (ex. Laravel, SaaS multi-tenant, SSO & RBAC)." />
                <x-admin.translated-field model="form.intro" label="Accroche (en-tête du site)" type="textarea" :rows="4" />
                <x-admin.translated-field model="form.about" label="À propos" type="textarea" :rows="6" />
                <x-admin.translated-field model="form.objective" label="Mission" type="textarea" :rows="3" />
                <x-admin.translated-field model="form.current_position" label="Poste actuel" />
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <flux:heading>Projet phare</flux:heading>
                <x-admin.translated-field model="form.featured_project" label="Description" type="textarea" :rows="3" />
                <flux:input wire:model="form.featured_project_url" type="url" label="Lien" placeholder="https://" />
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <flux:heading>Langues et centres d'intérêt</flux:heading>
                <flux:textarea wire:model="languages" label="Langues parlées" rows="4" description="Une langue par ligne, au format « Langue : niveau »." />
                <x-admin.translated-field model="interests" label="Centres d'intérêt" description="Séparés par des virgules." />
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <flux:heading>Référencement et partage</flux:heading>
                <x-admin.translated-field model="form.seo_title" label="Titre de la page d'accueil" description="70 caractères maximum." />
                <x-admin.translated-field model="form.seo_description" label="Méta-description" type="textarea" :rows="2" description="160 caractères maximum." />
                <div class="flex flex-wrap items-start gap-4">
                    @if ($ogImage?->isPreviewable())
                        <img src="{{ $ogImage->temporaryUrl() }}" alt="" class="h-24 w-44 rounded object-cover">
                    @elseif ($profile->og_image_path)
                        <img src="{{ $profile->mediaUrl($profile->og_image_path) }}" alt="" class="h-24 w-44 rounded object-cover">
                    @endif
                    <div class="flex-1">
                        <flux:input type="file" wire:model="ogImage" label="Image de partage (LinkedIn, Facebook, WhatsApp)" accept="image/jpeg,image/png" />
                        <flux:text class="mt-1 text-xs">1200 × 630 px conseillé, JPG ou PNG.</flux:text>
                        <flux:error name="ogImage" />
                    </div>
                </div>
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Photo et CV</flux:heading>
                <div class="flex items-center gap-4">
                    @if ($avatar?->isPreviewable())
                        <img src="{{ $avatar->temporaryUrl() }}" alt="" class="size-20 rounded-full object-cover">
                    @elseif ($profile->avatar_path)
                        <img src="{{ $profile->mediaUrl($profile->avatar_path) }}" alt="" class="size-20 rounded-full object-cover">
                    @else
                        <div class="flex size-20 items-center justify-center rounded-full bg-paper text-zinc-400 dark:bg-zinc-700"><flux:icon.user class="size-8" /></div>
                    @endif
                    <div class="flex-1">
                        <flux:input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/webp" />
                        <flux:error name="avatar" />
                    </div>
                </div>
                <div>
                    <flux:input type="file" wire:model="cv" label="CV (PDF, 5 Mo max.)" accept="application/pdf" />
                    <flux:error name="cv" />
                    @if ($profile->cv_path)
                        <flux:link :href="$profile->mediaUrl($profile->cv_path)" target="_blank" class="mt-2 inline-block text-sm">Voir le CV actuel</flux:link>
                    @else
                        <flux:text class="mt-2 text-xs">Aucun CV téléversé : l'ancien fichier du site est utilisé.</flux:text>
                    @endif
                </div>
                <flux:switch wire:model="form.is_available" label="Disponible pour de nouveaux projets" align="left" />
                <flux:input wire:model="form.years_experience" type="number" min="0" max="60" label="Années d'expérience affichées"
                    description="Vide : calculé automatiquement depuis le parcours." />
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <flux:heading>Coordonnées</flux:heading>
                <flux:input wire:model="form.email" type="email" label="Email public" required />
                <flux:input wire:model="form.phone" label="Téléphone" />
                <flux:input wire:model="form.whatsapp" label="WhatsApp" description="Format international sans « + » ni espaces." />
                <flux:input wire:model="form.location" label="Localisation" />
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <flux:heading>Réseaux</flux:heading>
                @foreach ($networks as $key => $label)
                    <flux:input wire:model="social.{{ $key }}" type="url" :label="$label" placeholder="https://" />
                @endforeach
            </flux:card>
        </div>
    </div>
</form>
