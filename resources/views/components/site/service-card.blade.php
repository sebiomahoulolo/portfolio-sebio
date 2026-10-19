@props(['service', 'delay' => 0, 'dark' => false, 'detailed' => false, 'wide' => false])

<a href="{{ lroute('services.show', ['slug' => $service->slugFor()]) }}"
    @class(['tile service-tile reveal', 'tile-dark' => $dark, 'span-2' => $wide]) style="--delay: {{ $delay }}s">
    <span class="service-icon"><i class="{{ $service->icon ?: 'fas fa-code' }}" aria-hidden="true"></i></span>
    <h3>{{ $service->translate('title') }}</h3>
    <p>{{ $service->translate('summary') }}</p>
    @if ($detailed && $service->featuresFor())
        <ul class="check-list">
            @foreach (array_slice($service->featuresFor(), 0, 4) as $feature)
                <li>{{ $feature }}</li>
            @endforeach
        </ul>
    @endif
    <span class="more">{{ __('En savoir plus') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
</a>
