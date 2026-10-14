<div class="flex flex-col gap-6">
    <x-admin.page-header title="Messages" description="Demandes reçues depuis le formulaire de contact du site." />

    <div class="flex flex-wrap items-end gap-3">
        <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass" placeholder="Nom, email, entreprise, sujet..." class="max-w-xs" clearable />
        <flux:select wire:model.live="status" class="max-w-44">
            <flux:select.option value="inbox">Boîte de réception</flux:select.option>
            @foreach ($statuses as $s)
                <flux:select.option :value="$s->value">{{ $s->label() }}s</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="type" class="max-w-48">
            <flux:select.option value="">Tous les types</flux:select.option>
            @foreach (App\Models\ContactMessage::TYPES as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if (count($selected) > 0)
        <div class="flex flex-wrap items-center gap-2 rounded-lg bg-paper px-4 py-2 dark:bg-zinc-700">
            <flux:text class="me-2 font-medium">{{ count($selected) }} sélectionné(s)</flux:text>
            <flux:button size="sm" wire:click="bulk('read')">Marquer comme lus</flux:button>
            <flux:button size="sm" wire:click="bulk('archive')">Archiver</flux:button>
            <flux:button size="sm" variant="danger" data-confirm="Supprimer définitivement les messages sélectionnés ?" data-confirm-text="Cette action est définitive." data-confirm-button="Supprimer" x-on:click="$confirm($el, () => $wire.bulk('delete'))">Supprimer</flux:button>
            <flux:button size="sm" variant="ghost" wire:click="$set('selected', [])">Annuler</flux:button>
        </div>
    @endif

    @if ($this->messages->isEmpty())
        <x-admin.empty-state icon="inbox" title="Aucun message" description="Les demandes envoyées depuis le site apparaîtront ici." />
    @else
        <flux:table :paginate="$this->messages">
            <flux:table.columns>
                <flux:table.column class="w-8"></flux:table.column>
                <flux:table.column>Expéditeur</flux:table.column>
                <flux:table.column>Sujet</flux:table.column>
                <flux:table.column>Statut</flux:table.column>
                <flux:table.column>Reçu</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->messages as $message)
                    <flux:table.row :key="$message->id" @class(['font-semibold' => $message->status === App\Enums\MessageStatus::New])>
                        <flux:table.cell><flux:checkbox wire:model.live="selected" :value="$message->id" /></flux:table.cell>
                        <flux:table.cell>
                            <a href="{{ route('admin.messages.show', $message) }}" wire:navigate class="block text-ink hover:text-copper-dark dark:text-paper">{{ $message->name }}</a>
                            <div class="text-xs font-normal text-zinc-500">{{ $message->email }}@if ($message->company) · {{ $message->company }}@endif</div>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-md">
                            <a href="{{ route('admin.messages.show', $message) }}" wire:navigate class="block truncate">{{ $message->subject }}</a>
                            @if ($message->type === 'quote')
                                <flux:badge size="sm" color="orange" class="mt-1">Devis{{ $message->budget ? ' · '.$message->budget : '' }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell><x-admin.status-badge :status="$message->status" /></flux:table.cell>
                        <flux:table.cell class="text-sm font-normal text-zinc-500" title="{{ $message->created_at->format('d/m/Y H:i') }}">{{ $message->created_at->diffForHumans() }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
