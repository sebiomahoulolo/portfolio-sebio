@props(['title' => null])

@php
    // [route, icône Heroicons, libellé, autorisation requise]
    $nav = [
        'Pilotage' => [
            ['admin.dashboard', 'squares-2x2', 'Tableau de bord', null],
            ['admin.analytics', 'chart-bar', 'Statistiques', 'view-analytics'],
        ],
        'Contenu' => [
            ['admin.profile.edit', 'identification', 'Profil public', 'manage-settings'],
            ['admin.projects.index', 'folder', 'Projets', null],
            ['admin.services.index', 'wrench-screwdriver', 'Services', null],
            ['admin.posts.index', 'newspaper', 'Blog', null],
            ['admin.technologies.index', 'code-bracket', 'Technologies', null],
            ['admin.career.index', 'academic-cap', 'Parcours', null],
        ],
        'Relation client' => [
            ['admin.messages.index', 'envelope', 'Messages', 'manage-contacts'],
            ['admin.testimonials.index', 'star', 'Avis et recommandations', null],
        ],
        'Sécurité' => [
            ['admin.account', 'shield-check', 'Mon compte', null],
            ['admin.activity', 'clipboard-document-list', "Journal d'activité", 'view-activity'],
            ['admin.backups', 'archive-box', 'Sauvegardes', 'manage-settings'],
        ],
    ];

    $badges = [
        'admin.messages.index' => Gate::allows('manage-contacts') ? \App\Models\ContactMessage::where('status', 'new')->count() : 0,
        'admin.testimonials.index' => \App\Models\Testimonial::where('status', 'pending')->count(),
    ];
@endphp

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ? $title.' · ' : '' }}Administration</title>
    @include('partials.favicons')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    {{-- « collapsible » : le menu se replie aussi sur ordinateur (icônes seules, infobulles). --}}
    <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-paper dark:border-zinc-700 dark:bg-ink">
        <flux:sidebar.header>
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 font-semibold text-ink dark:text-paper">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-copper text-sm font-bold text-white">MS</span>
                <span class="in-data-flux-sidebar-collapsed-desktop:hidden">Portfolio</span>
            </a>
            <flux:sidebar.collapse tooltip="Replier / déplier le menu" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            @foreach ($nav as $group => $items)
                @php
                    $visible = collect($items)->filter(fn ($item) => Route::has($item[0]) && (! $item[3] || Gate::allows($item[3])));
                @endphp

                @if ($visible->isNotEmpty())
                    {{-- Pas de flux:sidebar.group : Flux masque les groupes quand le menu est replié,
                         alors que les icônes doivent rester visibles. --}}
                    <div class="px-3 pt-4 pb-1 text-xs font-medium uppercase tracking-wide text-zinc-400 first:pt-0 in-data-flux-sidebar-collapsed-desktop:hidden">{{ $group }}</div>
                    <div class="hidden h-px bg-zinc-200 first:hidden in-data-flux-sidebar-collapsed-desktop:my-2 in-data-flux-sidebar-collapsed-desktop:block dark:bg-zinc-700"></div>
                    @foreach ($visible as [$route, $icon, $label])
                        <flux:sidebar.item
                            :icon="$icon"
                            :href="route($route)"
                            :current="request()->routeIs(substr_count($route, '.') > 1 ? Str::beforeLast($route, '.').'.*' : $route)"
                            :badge="($badges[$route] ?? 0) ?: null"
                            :tooltip="$label"
                            wire:navigate
                        >{{ $label }}</flux:sidebar.item>
                    @endforeach
                @endif
            @endforeach
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="globe-europe-africa" :href="route('home')" target="_blank" tooltip="Voir le site">Voir le site</flux:sidebar.item>
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
    <x-admin.dialogs />
    @fluxScripts
</body>
</html>
