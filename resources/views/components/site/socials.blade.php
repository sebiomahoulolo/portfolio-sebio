@props(['profile'])

@php
    $networks = [
        'linkedin' => ['fab fa-linkedin-in', 'LinkedIn'],
        'github' => ['fab fa-github', 'GitHub'],
        'facebook' => ['fab fa-facebook-f', 'Facebook'],
        'x' => ['fab fa-x-twitter', 'X'],
    ];
@endphp

<div class="socials">
    @foreach ($networks as $key => [$icon, $label])
        @if ($url = $profile->social($key))
            <a href="{{ $url }}" target="_blank" rel="noopener me" aria-label="{{ $label }}"><i class="{{ $icon }}" aria-hidden="true"></i></a>
        @endif
    @endforeach
    @if ($profile->whatsapp)
        <a href="https://wa.me/{{ $profile->whatsapp }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
    @endif
</div>
