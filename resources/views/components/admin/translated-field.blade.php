@props([
    'model',          // chemin wire:model, ex. « form.title »
    'label',
    'type' => 'input', // input | textarea
    'rows' => 4,
    'required' => false,
    'description' => null,
])

{{-- Champ bilingue : un onglet par langue, le français étant la langue de référence. --}}
<div x-data="{ locale: 'fr' }" class="flex flex-col gap-2">
    <div class="flex items-center justify-between gap-2">
        <flux:label>{{ $label }}@if ($required)<span class="text-copper-dark"> *</span>@endif</flux:label>
        <div @class(['flex gap-1 text-xs', 'hidden' => count(config('portfolio.locales')) < 2])>
            @foreach (config('portfolio.locales') as $code => $name)
                <button type="button" x-on:click="locale = '{{ $code }}'"
                    class="rounded px-2 py-0.5 font-medium uppercase"
                    :class="locale === '{{ $code }}' ? 'bg-ink text-paper dark:bg-paper dark:text-ink' : 'text-zinc-500 hover:text-ink dark:hover:text-paper'">
                    {{ $code }}
                </button>
            @endforeach
        </div>
    </div>

    @foreach (config('portfolio.locales') as $code => $name)
        <div x-show="locale === '{{ $code }}'" @if (! $loop->first) x-cloak @endif>
            @if ($type === 'textarea')
                <flux:textarea wire:model="{{ $model }}.{{ $code }}" :rows="$rows" :placeholder="$loop->first ? null : 'Traduction '.$name.' (facultatif)'" />
            @else
                <flux:input wire:model="{{ $model }}.{{ $code }}" :placeholder="$loop->first ? null : 'Traduction '.$name.' (facultatif)'" />
            @endif
            <flux:error name="{{ $model }}.{{ $code }}" />
        </div>
    @endforeach

    @if ($description)
        <flux:text class="text-xs">{{ $description }}</flux:text>
    @endif
</div>
