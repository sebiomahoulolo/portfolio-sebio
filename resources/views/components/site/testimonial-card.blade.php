@props(['testimonial', 'delay' => 0, 'dark' => false, 'span' => 2])

<figure @class(['tile quote-tile reveal', 'tile-dark' => $dark, 'span-'.$span => $span > 1]) style="margin: 0; --delay: {{ $delay }}s">
    <div>
        @if ($testimonial->rating)
            <div class="stars" role="img" aria-label="{{ __('Note : :rating sur 5', ['rating' => $testimonial->rating]) }}" style="margin-bottom: 14px;">
                @for ($i = 1; $i <= 5; $i++)
                    <i @class(['fas fa-star', 'off' => $i > $testimonial->rating]) aria-hidden="true"></i>
                @endfor
            </div>
        @endif
        <blockquote>{{ $testimonial->translate('quote') }}</blockquote>
    </div>
    <figcaption class="author">
        <span class="avatar">
            @if ($testimonial->avatarUrl())
                <img src="{{ $testimonial->avatarUrl() }}" alt="" loading="lazy">
            @else
                {{ $testimonial->initials() }}
            @endif
        </span>
        <span>
            <strong>{{ $testimonial->author_name }}</strong>
            <span>{{ $testimonial->translate('author_title') }}</span>
        </span>
        @if ($testimonial->source === 'linkedin')
            <span class="source"><i class="fab fa-linkedin" aria-hidden="true"></i> LinkedIn</span>
        @elseif ($testimonial->source === 'site')
            <span class="source" style="color: var(--muted);"><i class="fas fa-circle-check" aria-hidden="true" style="color: #16A34A;"></i> {{ __('Avis vérifié') }}</span>
        @endif
    </figcaption>
</figure>
