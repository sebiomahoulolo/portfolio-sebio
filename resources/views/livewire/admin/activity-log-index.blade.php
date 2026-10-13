<div class="flex flex-col gap-6">
    <x-admin.page-header title="Journal d'activité" description="Connexions, publications, suppressions et autres actions importantes." />

    @if ($logs->isEmpty())
        <x-admin.empty-state icon="clipboard-document-list" title="Aucune activité enregistrée" />
    @else
        <flux:table :paginate="$logs">
            <flux:table.columns>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Utilisateur</flux:table.column>
                <flux:table.column>Action</flux:table.column>
                <flux:table.column>Détail</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($logs as $log)
                    <flux:table.row :key="$log->id">
                        <flux:table.cell class="text-sm text-zinc-500">{{ $log->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                        <flux:table.cell>{{ $log->user?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell><code class="text-xs">{{ $log->action }}</code></flux:table.cell>
                        <flux:table.cell class="max-w-lg truncate">{{ $log->description }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
