@props(['label', 'value', 'hint' => null, 'href' => null])

<flux:card class="flex flex-col gap-1">
    <flux:text class="text-sm">{{ $label }}</flux:text>
    <div class="text-3xl font-semibold tabular-nums text-ink dark:text-paper">{{ $value }}</div>
    @if ($hint || $href)
        <div class="flex items-center justify-between gap-2 text-sm">
            @if ($hint)<flux:text class="text-xs">{{ $hint }}</flux:text>@endif
            @if ($href)<flux:link :href="$href" wire:navigate class="text-xs">Voir</flux:link>@endif
        </div>
    @endif
</flux:card>
