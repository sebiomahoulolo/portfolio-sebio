{{-- Pages d'erreur : autonomes (aucun accès à la base) pour s'afficher même si le serveur est en difficulté. --}}
@php
    if (request()->is('en', 'en/*')) {
        app()->setLocale('en');
    }
    $admin = request()->is('admin', 'admin/*');
    $homeUrl = $admin && auth()->check() ? url('/admin') : (app()->getLocale() === 'en' ? url('/en') : url('/'));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') · {{ __($__env->yieldContent('title')) }}</title>
    @include('partials.favicons')
    <script>
        (function () {
            var t; try { t = localStorage.getItem('theme'); } catch (e) {}
            if (t !== 'dark' && t !== 'light') t = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <style>
        :root { --bg: #F5F3EF; --card: #FFFFFF; --text: #111111; --muted: #6B7280; --line: rgba(17,17,17,.1); --accent: #D97745; }
        [data-theme="dark"] { --bg: #0B0B0C; --card: #151517; --text: #F5F3EF; --muted: #A1A1AA; --line: rgba(255,255,255,.1); }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--bg); color: var(--text); font: 16px/1.6 Inter, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .card { width: 100%; max-width: 520px; padding: 40px 32px; border-radius: 26px; background: var(--card); border: 1px solid var(--line); box-shadow: 0 30px 60px -30px rgba(17,17,17,.3); text-align: center; }
        .mark { display: inline-grid; place-items: center; width: 52px; height: 52px; border-radius: 14px; background: var(--accent); color: #111111; font-weight: 700; letter-spacing: .02em; text-decoration: none; }
        .code { margin: 26px 0 0; font: 600 .8rem/1 'JetBrains Mono', ui-monospace, Consolas, monospace; letter-spacing: .14em; color: var(--accent); }
        h1 { margin: 10px 0 12px; font: 700 clamp(1.5rem, 4vw, 2rem)/1.2 'Space Grotesk', Inter, system-ui, sans-serif; }
        p { margin: 0 auto; max-width: 40ch; color: var(--muted); }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 28px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; border-radius: 999px; border: 1px solid var(--line); background: transparent; color: var(--text); font: inherit; font-size: .92rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: background-color .2s, color .2s; }
        .btn-primary { background: var(--text); border-color: var(--text); color: var(--bg); }
        .btn:hover { background: var(--accent); border-color: var(--accent); color: #FFFFFF; }
    </style>
</head>
<body>
    <main class="card">
        <a href="{{ $homeUrl }}" class="mark" aria-label="{{ __('Accueil') }}">MS</a>
        <p class="code">{{ __('ERREUR') }} @yield('code')</p>
        <h1>{{ __($__env->yieldContent('title')) }}</h1>
        <p>{{ __($__env->yieldContent('message')) }}</p>
        <div class="actions">
            @hasSection('actions')
                @yield('actions')
            @else
                <a href="{{ $homeUrl }}" class="btn btn-primary">{{ $admin && auth()->check() ? __('Retour au tableau de bord') : __('Retour à l’accueil') }}</a>
                <button type="button" class="btn" onclick="history.length > 1 ? history.back() : location.assign('{{ $homeUrl }}')">{{ __('Page précédente') }}</button>
            @endif
        </div>
    </main>
</body>
</html>
