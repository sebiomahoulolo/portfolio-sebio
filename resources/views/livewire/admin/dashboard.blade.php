<div class="flex flex-col gap-8">
    <x-admin.page-header :title="'Bonjour '.Str::before(auth()->user()->name, ' ')" description="Vue d'ensemble de votre portfolio.">
        <x-slot:actions>
            @can('create', App\Models\Project::class)
                <flux:button variant="primary" icon="plus" :href="route('admin.projects.create')" wire:navigate>Nouveau projet</flux:button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('two_factor_warning'))
        <flux:callout variant="warning" icon="key" :heading="session('two_factor_warning')" />
    @endif

    @unless (auth()->user()->hasTwoFactorEnabled())
        <flux:callout variant="warning" icon="shield-exclamation" heading="Protégez votre compte avec la double authentification">
            <flux:callout.text>Aujourd’hui, votre mot de passe suffit pour entrer. Activez un code de vérification sur votre téléphone : cela prend deux minutes.</flux:callout.text>
            <x-slot name="actions">
                <flux:button size="sm" :href="route('admin.account')" wire:navigate>Activer maintenant</flux:button>
            </x-slot>
        </flux:callout>
    @endunless

    <div @class(['grid gap-4 sm:grid-cols-2', 'xl:grid-cols-5' => $canSeeContacts, 'xl:grid-cols-4' => ! $canSeeContacts])>
        <x-admin.stat-card label="Visiteurs" icon="users" :value="number_format($totalVisitors, 0, ',', ' ')"
            :hint="$visitorsToday.' aujourd’hui · '.$onlineNow.' en ligne'" :href="$canSeeAnalytics ? route('admin.analytics') : null" />
        <x-admin.stat-card label="Consultations de projets" icon="eye" :value="number_format($projectViews, 0, ',', ' ')" hint="Pages détail des projets" modal="project-views" />
        <x-admin.stat-card label="Projets publiés" icon="briefcase" :value="$publishedProjects" :hint="$draftProjects.' brouillon(s)'" :href="route('admin.projects.index')" />
        <x-admin.stat-card label="Technologies" icon="code-bracket" :value="$technologies" :href="route('admin.technologies.index')" />
        @if ($canSeeContacts)
            <x-admin.stat-card label="Nouveaux messages" icon="envelope" :value="$newMessages" :href="route('admin.messages.index')" />
        @endif
    </div>

    {{-- Détail des consultations, projet par projet. --}}
    <flux:modal name="project-views" class="w-full max-w-xl">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Consultations par projet</flux:heading>
                <flux:text class="mt-1">Nombre d’ouvertures de la page détail de chaque projet depuis sa mise en ligne.</flux:text>
            </div>
            @php $maxViews = max(1, (int) $projectViewsDetail->max('views_count')); @endphp
            <ul class="flex flex-col gap-4">
                @forelse ($projectViewsDetail as $project)
                    <li class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <a href="{{ route('admin.projects.edit', $project) }}" wire:navigate class="truncate font-medium text-ink hover:text-copper-dark dark:text-paper">{{ $project->translate('title') }}</a>
                            <div class="flex shrink-0 items-center gap-3">
                                <span class="font-semibold tabular-nums text-ink dark:text-paper">{{ number_format($project->views_count, 0, ',', ' ') }}</span>
                                @if ($project->isPubliclyVisible())
                                    <a href="{{ lroute('projects.show', ['slug' => $project->slug], 'fr') }}" target="_blank" class="text-zinc-400 hover:text-copper-dark" title="Voir la page publique">
                                        <flux:icon name="arrow-top-right-on-square" class="size-4" />
                                    </a>
                                @else
                                    <x-admin.status-badge :status="$project->status" />
                                @endif
                            </div>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div class="h-full rounded-full bg-copper" style="width: {{ round($project->views_count / $maxViews * 100) }}%"></div>
                        </div>
                    </li>
                @empty
                    <flux:text>Aucun projet.</flux:text>
                @endforelse
            </ul>
        </div>
    </flux:modal>

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
