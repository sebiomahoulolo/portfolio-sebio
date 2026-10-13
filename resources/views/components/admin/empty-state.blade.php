@props(['icon' => 'inbox', 'title', 'description' => null])

<div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 px-6 py-12 text-center dark:border-zinc-600">
    <flux:icon :name="$icon" class="size-8 text-zinc-400" />
    <flux:heading>{{ $title }}</flux:heading>
    @if ($description)
        <flux:text>{{ $description }}</flux:text>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-3">{{ $slot }}</div>
    @endif
</div>
