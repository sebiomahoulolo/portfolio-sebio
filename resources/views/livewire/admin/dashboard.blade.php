<div class="flex flex-col gap-8">
    <x-admin.page-header :title="'Bonjour '.Str::before(auth()->user()->name, ' ')" description="Vue d'ensemble de votre portfolio.">
        <x-slot:actions>
            @can('create', App\Models\Project::class)
                <flux:button variant="primary" icon="plus" :href="route('admin.projects.create')" wire:navigate>Nouveau projet</flux:button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Projets publiés" :value="$publishedProjects" :hint="$draftProjects.' brouillon(s)'" :href="route('admin.projects.index')" />
        <x-admin.stat-card label="Consultations de projets" :value="number_format($projectViews, 0, ',', ' ')" hint="Pages détail /projets" />
        <x-admin.stat-card label="Technologies" :value="$technologies" :href="route('admin.technologies.index')" />
        @if ($canSeeContacts)
            <x-admin.stat-card label="Nouveaux messages" :value="$newMessages" :href="route('admin.messages.index')" />
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @if ($canSeeContacts)
            <flux:card class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <flux:heading>Derniers messages</flux:heading>
                    <flux:link :href="route('admin.messages.index')" wire:navigate class="text-sm">Tout voir</flux:link>
                </div>
                @forelse ($latestMessages as $message)
                    <a href="{{ route('admin.messages.show', $message) }}" wire:navigate class="flex items-start justify-between gap-3 rounded-lg p-2 -mx-2 hover:bg-paper dark:hover:bg-zinc-700">
                        <div class="min-w-0">
                            <div @class(['truncate text-sm text-ink dark:text-paper', 'font-semibold' => $message->status === App\Enums\MessageStatus::New])>{{ $message->name }} — {{ $message->subject }}</div>
                            <div class="truncate text-xs text-zinc-500">{{ Str::limit($message->message, 90) }}</div>
                        </div>
                        <span class="shrink-0 text-xs text-zinc-500">{{ $message->created_at->diffForHumans(short: true) }}</span>
                    </a>
                @empty
                    <flux:text>Aucun message pour le moment.</flux:text>
                @endforelse
            </flux:card>
        @endif

        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading>Projets récemment modifiés</flux:heading>
                <flux:link :href="route('admin.projects.index')" wire:navigate class="text-sm">Tout voir</flux:link>
            </div>
            @forelse ($recentProjects as $project)
                <a href="{{ route('admin.projects.edit', $project) }}" wire:navigate class="flex items-center justify-between gap-3 rounded-lg p-2 -mx-2 hover:bg-paper dark:hover:bg-zinc-700">
                    <span class="truncate text-sm text-ink dark:text-paper">{{ $project->translate('title') }}</span>
                    <x-admin.status-badge :status="$project->status" />
                </a>
            @empty
                <flux:text>Aucun projet.</flux:text>
            @endforelse
        </flux:card>

        @if ($activity->isNotEmpty())
            <flux:card class="flex flex-col gap-4 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <flux:heading>Activité récente</flux:heading>
                    <flux:link :href="route('admin.activity')" wire:navigate class="text-sm">Journal complet</flux:link>
                </div>
                <ul class="flex flex-col gap-2 text-sm">
                    @foreach ($activity as $log)
                        <li class="flex justify-between gap-3">
                            <span class="truncate"><span class="font-medium">{{ $log->user?->name ?? 'Système' }}</span> · {{ $log->description }}</span>
                            <span class="shrink-0 text-xs text-zinc-500">{{ $log->created_at->diffForHumans(short: true) }}</span>
                        </li>
                    @endforeach
                </ul>
            </flux:card>
        @endif
    </div>
</div>
