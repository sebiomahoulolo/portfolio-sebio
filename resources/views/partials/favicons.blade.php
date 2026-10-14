{{-- Favicons communs : site public, administration, connexion et pages d'erreur. --}}
@php $iconVersion = @filemtime(public_path('favicon.ico')) ?: 1; @endphp
<link rel="icon" href="{{ asset('favicon.ico') }}?v={{ $iconVersion }}" sizes="48x48">
<link rel="icon" href="{{ asset('favicon.svg') }}?v={{ $iconVersion }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ $iconVersion }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}?v={{ $iconVersion }}">
