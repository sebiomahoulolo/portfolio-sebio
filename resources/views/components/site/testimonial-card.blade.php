@props(['testimonial', 'delay' => 0, 'dark' => false, 'span' => 2])

<figure @class(['tile quote-tile reveal', 'tile-dark' => $dark, 'span-'.$span => $span > 1]) style="margin: 0; --delay: {{ $delay }}s">
    <blockquote>{{ $testimonial->translate('quote') }}</blockquote>
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
        @endif
    </figcaption>
</figure>
