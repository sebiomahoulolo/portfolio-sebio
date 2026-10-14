@props(['title' => null])

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
<body class="min-h-screen bg-paper antialiased dark:bg-ink">
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-6">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium text-ink dark:text-paper">
                <span class="flex size-10 items-center justify-center rounded-md bg-copper font-bold text-ink">MS</span>
                <span class="text-sm">Portfolio · Administration</span>
            </a>
            <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                {{ $slot }}
            </div>
        </div>
    </div>
    <x-admin.dialogs />
    @fluxScripts
</body>
</html>
