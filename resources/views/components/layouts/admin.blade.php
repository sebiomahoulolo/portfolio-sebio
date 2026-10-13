@props(['title' => null])

@php
    $nav = [
        'Contenu' => [
            ['admin.dashboard', 'home', 'Tableau de bord', null],
            ['admin.profile.edit', 'user-circle', 'Profil public', 'manage-settings'],
            ['admin.projects.index', 'rectangle-stack', 'Projets', null],
            ['admin.technologies.index', 'cpu-chip', 'Technologies', null],
        ],
        'Contacts' => [
            ['admin.messages.index', 'inbox', 'Messages', 'manage-contacts'],
            ['admin.activity', 'clipboard-document-list', "Journal d'activité", 'view-activity'],
        ],
    ];

    $unreadMessages = Gate::allows('manage-contacts')
        ? \App\Models\ContactMessage::where('status', 'new')->count()
        : 0;
@endphp

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ? $title.' · ' : '' }}Administration</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-paper dark:border-zinc-700 dark:bg-ink">
        <flux:sidebar.header>
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 font-semibold text-ink dark:text-paper">
                <span class="flex size-8 items-center justify-center rounded-md bg-copper text-sm font-bold text-ink">MS</span>
                <span>Portfolio</span>
            </a>
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            @foreach ($nav as $group => $items)
                @php
                    $visible = collect($items)->filter(fn ($item) => Route::has($item[0]) && (! $item[3] || Gate::allows($item[3])));
                @endphp

                @if ($visible->isNotEmpty())
                    <flux:sidebar.group :heading="$group" class="grid">
                        @foreach ($visible as [$route, $icon, $label])
                            <flux:sidebar.item
                                :icon="$icon"
                                :href="route($route)"
                                :current="request()->routeIs(substr_count($route, '.') > 1 ? Str::beforeLast($route, '.').'.*' : $route)"
                                :badge="$route === 'admin.messages.index' && $unreadMessages ? $unreadMessages : null"
                                wire:navigate
                            >{{ $label }}</flux:sidebar.item>
                        @endforeach
                    </flux:sidebar.group>
                @endif
            @endforeach
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="globe-alt" :href="route('home')" target="_blank">Voir le site</flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile :name="auth()->user()->name" :initials="Str::of(auth()->user()->name)->explode(' ')->map(fn ($part) => Str::substr($part, 0, 1))->take(2)->implode('')" />
            <flux:menu>
                <flux:menu.item icon="cog-6-tooth" :href="route('admin.account')" wire:navigate>Mon compte</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">Se déconnecter</flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <flux:dropdown position="top" align="end">
            <flux:profile :initials="Str::substr(auth()->user()->name, 0, 1)" icon-trailing="chevron-down" />
            <flux:menu>
                <flux:menu.item icon="cog-6-tooth" :href="route('admin.account')" wire:navigate>Mon compte</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">Se déconnecter</flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:main container>
        {{ $slot }}
    </flux:main>

    <flux:toast />
    @fluxScripts
</body>
</html>
