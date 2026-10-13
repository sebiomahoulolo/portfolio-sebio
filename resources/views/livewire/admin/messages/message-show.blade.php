@php
    $replySubject = rawurlencode('Re : '.$message->subject);
@endphp

<div class="flex max-w-3xl flex-col gap-6">
    <x-admin.page-header :title="$message->subject">
        <x-slot:actions>
            <flux:button variant="ghost" icon="arrow-left" :href="route('admin.messages.index')" wire:navigate>Messages</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <flux:card class="flex flex-col gap-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="font-semibold text-ink dark:text-paper">{{ $message->name }}</div>
                <div class="text-sm text-zinc-500">
                    {{ $message->email }}
                    @if ($message->phone) · {{ $message->phone }}@endif
                    @if ($message->company) · {{ $message->company }}@endif
                </div>
            </div>
            <div class="flex flex-col items-end gap-1 text-sm text-zinc-500">
                <x-admin.status-badge :status="$message->status" />
                <span>{{ $message->created_at->format('d/m/Y à H:i') }}</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:badge size="sm">{{ App\Models\ContactMessage::TYPES[$message->type] ?? $message->type }}</flux:badge>
            @if ($message->budget)
                <flux:badge size="sm" color="orange">Budget : {{ $message->budget }}</flux:badge>
            @endif
        </div>

        <flux:separator />

        <div class="whitespace-pre-line leading-relaxed text-zinc-800 dark:text-zinc-200">{{ $message->message }}</div>

        <flux:separator />

        <div class="flex flex-wrap gap-2">
            <flux:button variant="primary" icon="envelope" href="mailto:{{ $message->email }}?subject={{ $replySubject }}">Répondre par email</flux:button>
            @if ($message->phone)
                <flux:button icon="chat-bubble-left-right" href="https://wa.me/{{ preg_replace('/\D/', '', $message->phone) }}" target="_blank">WhatsApp</flux:button>
            @endif
            <flux:spacer />
            @if ($message->status === App\Enums\MessageStatus::Archived)
                <flux:button wire:click="setStatus('read')">Désarchiver</flux:button>
            @else
                <flux:button wire:click="setStatus('archived')" icon="archive-box">Archiver</flux:button>
            @endif
            <flux:button variant="danger" icon="trash" wire:click="delete" wire:confirm="Supprimer définitivement ce message ?">Supprimer</flux:button>
        </div>
    </flux:card>
</div>
