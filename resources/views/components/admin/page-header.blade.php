@props(['title', 'description' => null])

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
        @if ($description)
            <flux:text class="mt-1">{{ $description }}</flux:text>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
