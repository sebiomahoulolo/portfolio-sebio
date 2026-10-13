@props(['post', 'delay' => 0, 'wide' => false, 'dark' => false])

<a href="{{ lroute('blog.show', ['slug' => $post->slug]) }}" @class(['tile post-tile reveal', 'span-2' => $wide, 'tile-dark' => $dark]) style="--delay: {{ $delay }}s">
    @if ($post->coverUrl())
        <div class="post-cover"><img src="{{ $post->coverUrl() }}" alt="" loading="lazy"></div>
    @endif
    <div class="post-meta">
        <span><i class="far fa-calendar" aria-hidden="true"></i> {{ $post->published_at->translatedFormat('d M Y') }}</span>
        <span><i class="far fa-clock" aria-hidden="true"></i> {{ trans_choice(':count min de lecture|:count min de lecture', $post->readingMinutes()) }}</span>
    </div>
    <h3>{{ $post->translate('title') }}</h3>
    <p>{{ $post->translate('excerpt') }}</p>
    @if ($post->tags)
        <div class="tags">
            @foreach (array_slice($post->tags, 0, 3) as $tag)
                <span class="tag">{{ $tag }}</span>
            @endforeach
        </div>
    @endif
    <span class="more">{{ __('Lire l’article') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
</a>
