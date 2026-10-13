@props(['label', 'value', 'hint' => null, 'href' => null, 'icon' => null, 'modal' => null])

<flux:card class="flex flex-col gap-1">
    <div class="flex items-center justify-between gap-2">
        <flux:text class="text-sm">{{ $label }}</flux:text>
        @if ($icon)<flux:icon :name="$icon" class="size-5 text-copper-dark" />@endif
    </div>
    <div class="text-3xl font-semibold tabular-nums text-ink dark:text-paper">{{ $value }}</div>
    @if ($hint || $href || $modal)
        <div class="flex items-center justify-between gap-2 text-sm">
            @if ($hint)<flux:text class="text-xs">{{ $hint }}</flux:text>@endif
            @if ($modal)
                <flux:modal.trigger :name="$modal">
                    <button type="button" class="shrink-0 cursor-pointer text-xs font-medium text-copper-dark hover:underline">Voir le détail</button>
                </flux:modal.trigger>
            @elseif ($href)
                <flux:link :href="$href" wire:navigate class="shrink-0 text-xs">Voir</flux:link>
            @endif
        </div>
    @endif
</flux:card>
