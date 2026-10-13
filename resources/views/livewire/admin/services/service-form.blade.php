<form wire:submit="save" class="flex flex-col gap-6">
    <x-admin.page-header :title="$serviceId ? 'Modifier le service' : 'Nouveau service'"
        description="Chaque service a sa propre page publique, en français et en anglais.">
        <x-slot:actions>
            <flux:button variant="ghost" :href="route('admin.services.index')" wire:navigate>Retour à la liste</flux:button>
            <flux:button variant="primary" type="submit" icon="check">Enregistrer</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Présentation</flux:heading>
                <x-admin.translated-field model="form.title" label="Titre" required />
                <x-admin.translated-field model="form.summary" label="Accroche" type="textarea" :rows="2" description="Une phrase, affichée sur la carte du service." />
                <x-admin.translated-field model="form.description" label="Description détaillée" type="textarea" :rows="8"
                    description="Texte de la page du service. « ## » crée un intertitre, « - » une liste." />
            </flux:card>

            <flux:card class="flex flex-col gap-3" x-data="{ locale: 'fr' }">
                <div class="flex items-center justify-between">
                    <flux:heading>Ce qui est inclus</flux:heading>
                    <div class="flex gap-1 text-xs">
                        @foreach (config('portfolio.locales') as $code => $name)
                            <button type="button" x-on:click="locale = '{{ $code }}'" class="rounded px-2 py-0.5 font-medium uppercase"
                                :class="locale === '{{ $code }}' ? 'bg-ink text-paper dark:bg-paper dark:text-ink' : 'text-zinc-500'">{{ $code }}</button>
                        @endforeach
                    </div>
                </div>
                @foreach (config('portfolio.locales') as $code => $name)
                    <div x-show="locale === '{{ $code }}'" @if (! $loop->first) x-cloak @endif>
                        <flux:textarea wire:model="features.{{ $code }}" rows="6" placeholder="Un élément par ligne" />
                    </div>
                @endforeach
                <flux:text class="text-xs">Un élément par ligne. Affichés avec une coche sur le site.</flux:text>
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <flux:heading>Référencement</flux:heading>
                <x-admin.translated-field model="form.seo_description" label="Méta-description" type="textarea" :rows="2" description="160 caractères maximum. Par défaut : l'accroche." />
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Publication</flux:heading>
                <flux:switch wire:model="form.is_published" label="Visible sur le site" align="left" />
                <flux:input wire:model="form.sort_order" type="number" min="0" label="Ordre d'affichage" />
                <flux:input wire:model.blur="form.slug" label="Adresse (slug)" placeholder="Générée à partir du titre" description="/services/{{ $form['slug'] ?: '...' }}" />
            </flux:card>
            <flux:card class="flex flex-col gap-5">
                <flux:heading>Affichage</flux:heading>
                <div class="flex items-end gap-3">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-paper text-lg text-copper-dark dark:bg-zinc-700"><i class="{{ $form['icon'] ?: 'fas fa-code' }}"></i></span>
                    <flux:input wire:model.live.debounce.500ms="form.icon" label="Icône Font Awesome" placeholder="fas fa-globe" class="flex-1" />
                </div>
                <x-admin.translated-field model="form.price_label" label="Tarif affiché" description="Ex. « Sur devis », « À partir de 300 000 FCFA »." />
            </flux:card>
        </div>
    </div>
</form>
