<div class="flex flex-col gap-8">
    <x-admin.page-header title="Sauvegardes" description="La base de données et les fichiers téléversés sont sauvegardés automatiquement chaque nuit à 3 h.">
        <x-slot:actions>
            <flux:button variant="primary" icon="archive-box-arrow-down" wire:click="create" wire:loading.attr="disabled" wire:target="create">
                <span wire:loading.remove wire:target="create">Sauvegarder maintenant</span>
                <span wire:loading wire:target="create">Sauvegarde en cours…</span>
            </flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-admin.stat-card label="Sauvegardes disponibles" icon="archive-box" :value="$backups->count()" :hint="'Conservées '.$keepDays.' jours'" />
        <x-admin.stat-card label="Dernière sauvegarde" icon="clock"
            :value="$latest ? $latest['created_at']->diffForHumans() : '—'"
            :hint="$latest ? $latest['created_at']->format('d/m/Y à H:i') : 'Aucune pour le moment'" />
        <x-admin.stat-card label="Chiffrement" icon="lock-closed" :value="$encrypted ? 'AES-256' : 'Désactivé'"
            :hint="$encrypted ? 'Archives protégées par mot de passe' : 'Définissez BACKUP_PASSWORD'" />
    </div>

    @if ($latest && $latest['created_at']->lt(now()->subDays(2)))
        <flux:callout variant="danger" icon="exclamation-triangle" heading="Aucune sauvegarde depuis plus de 48 heures">
            <flux:callout.text>La tâche planifiée ne semble pas s’exécuter. Vérifiez la tâche Cron dans hPanel (Avancé → Tâches Cron) : « php artisan schedule:run » chaque minute.</flux:callout.text>
        </flux:callout>
    @endif

    @unless ($encrypted)
        <flux:callout variant="warning" icon="lock-open" heading="Les archives ne sont pas chiffrées">
            <flux:callout.text>Elles contiennent les messages reçus et les comptes. Ajoutez BACKUP_PASSWORD dans le fichier .env du serveur pour les protéger, surtout si vous les téléchargez.</flux:callout.text>
        </flux:callout>
    @endunless

    @if ($backups->isEmpty())
        <x-admin.empty-state icon="archive-box" title="Aucune sauvegarde" description="La première sera créée cette nuit, ou maintenant avec le bouton « Sauvegarder maintenant »." />
    @else
        <flux:card class="p-0">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Taille</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($backups as $item)
                        <flux:table.row :key="$item['name']">
                            <flux:table.cell class="ps-4">
                                <div class="font-medium text-ink dark:text-paper">{{ $item['created_at']->format('d/m/Y à H:i') }}</div>
                                <div class="text-xs text-zinc-500">{{ $item['name'] }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ \Illuminate\Support\Number::fileSize($item['size'], 1) }}</flux:table.cell>
                            <flux:table.cell align="end" class="pe-4">
                                <flux:button size="sm" variant="ghost" icon="arrow-down-tray" wire:click="download('{{ $item['name'] }}')">Télécharger</flux:button>
                                <flux:button size="sm" variant="ghost" icon="trash" tooltip="Supprimer"
                                    data-confirm="Supprimer cette sauvegarde ?"
                                    data-confirm-text="L’archive du {{ $item['created_at']->format('d/m/Y à H:i') }} sera définitivement effacée."
                                    data-confirm-button="Supprimer"
                                    x-on:click="$confirm($el, () => $wire.delete('{{ $item['name'] }}'))" />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif

    <flux:text class="text-sm">
        Gardez aussi une copie hors du serveur : téléchargez une sauvegarde chaque mois et rangez-la sur un disque ou un cloud personnel.
        Pour restaurer, suivez le fichier LISEZ-MOI.txt inclus dans chaque archive.
    </flux:text>
</div>
