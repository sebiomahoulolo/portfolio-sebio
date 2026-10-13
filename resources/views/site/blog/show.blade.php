@php
    $title = $post->translate('title');
    $url = lroute('blog.show', ['slug' => $post->slug]);
    $shareText = rawurlencode($title);
    $shareUrl = rawurlencode($url);
@endphp

<x-layouts.site :profile="$profile"
    :title="($post->translate('seo_title') ?: $title).' — '.$profile->name"
    :description="$post->translate('seo_description') ?: $post->translate('excerpt')"
    :image="$post->coverUrl()"
    :indexable="$post->is_indexable"
    type="article">

    <x-slot:head>
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $title,
                'description' => $post->translate('excerpt'),
                'url' => $url,
                'image' => $post->coverUrl(),
                'datePublished' => $post->published_at?->toAtomString(),
                'dateModified' => $post->updated_at?->toAtomString(),
                'inLanguage' => app()->getLocale(),
                'keywords' => implode(', ', $post->tags ?? []),
                'author' => ['@type' => 'Person', 'name' => $profile->name, 'url' => lroute('home')],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>

    <section class="page-head">
        <div class="container bento">
            <div class="tile tile-dark page-title span-4 reveal is-visible">
                <nav class="breadcrumb" aria-label="{{ __("Fil d'Ariane") }}">
                    <a href="{{ lroute('home') }}">{{ __('Accueil') }}</a><span aria-hidden="true">/</span>
                    <a href="{{ lroute('blog.index') }}">{{ __('Blog') }}</a><span aria-hidden="true">/</span>
                    <span>{{ Str::limit($title, 40) }}</span>
                </nav>
                <h1 style="max-width: 24ch;">{{ $title }}</h1>
                @if ($post->translate('excerpt'))<p class="lead">{{ $post->translate('excerpt') }}</p>@endif
                <div class="post-meta" style="margin-top: 22px;">
                    <span><img src="{{ $profile->photoUrl() }}" alt="" class="author-chip"> {{ $profile->name }}</span>
                    <span><i class="far fa-calendar" aria-hidden="true"></i> {{ $post->published_at->translatedFormat('d F Y') }}</span>
                    <span><i class="far fa-clock" aria-hidden="true"></i> {{ trans_choice(':count min de lecture|:count min de lecture', $post->readingMinutes()) }}</span>
                </div>
            </div>
            @if ($post->coverUrl())
                <div class="tile tile-flat cover-tile span-4 reveal">
                    <img src="{{ $post->coverUrl() }}" alt="" width="1600" height="800">
                </div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="container bento">
            <article class="tile span-3 reveal article-body">
                <x-site.rich-text :text="$post->translate('body')" />
            </article>

            <div class="article-aside">
            <aside class="tile reveal share-tile">
                <span class="tile-label"><i class="fas fa-share-nodes" aria-hidden="true"></i> {{ __('Partager') }}</span>
                <div class="socials" style="margin-top: 14px;">
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" aria-label="X"><i class="fab fa-x-twitter" aria-hidden="true"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                    <button type="button" class="copy-link" data-copy="{{ $url }}" data-copied="{{ __('Lien copié') }}" aria-label="{{ __('Copier le lien') }}"><i class="fas fa-link" aria-hidden="true"></i></button>
                </div>
                @if ($post->tags)
                    <span class="tile-label" style="margin-top: 22px;"><i class="fas fa-tags" aria-hidden="true"></i> {{ __('Thèmes') }}</span>
                    <div class="tags" style="margin-top: 12px;">
                        @foreach ($post->tags as $tag)
                            <a class="tag" href="{{ lroute('blog.index', ['tag' => $tag]) }}">{{ $tag }}</a>
                        @endforeach
                    </div>
                @endif
            </aside>

            <a href="{{ lroute('services.index') }}" class="tile tile-copper stat reveal is-compact">
                <i class="fas fa-handshake watermark" aria-hidden="true"></i>
                <span class="tile-label"><i class="fas fa-lightbulb" aria-hidden="true"></i> {{ __('Besoin d’aide ?') }}</span>
                <span class="tile-corner" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                <h3>{{ __('Je conçois et sécurise ce type de plateforme pour vous.') }}</h3>
            </a>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <div class="container" style="margin-top: clamp(48px, 7vw, 80px);">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow">{{ __('À lire aussi') }}</span>
                        <h2>{{ __('Articles similaires') }}</h2>
                    </div>
                </div>
                <div class="bento">
                    @foreach ($related as $item)
                        <x-site.post-card :post="$item" :wide="true" :delay="$loop->index * 0.06" />
                    @endforeach
                </div>
            </div>
        @endif
    </section>
</x-layouts.site>
