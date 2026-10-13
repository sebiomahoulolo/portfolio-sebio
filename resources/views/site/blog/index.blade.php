<x-layouts.site :profile="$profile"
    :title="__('Blog').($activeTag ? ' · '.$activeTag : '').' — '.$profile->name"
    :description="__('Articles techniques sur Laravel, la sécurité des applications et les plateformes SaaS.')"
    :indexable="! $activeTag && $posts->currentPage() === 1">

    <section class="page-head">
        <div class="container bento">
            <div class="tile span-3 reveal is-visible">
                <nav class="breadcrumb" aria-label="{{ __("Fil d'Ariane") }}">
                    <a href="{{ lroute('home') }}">{{ __('Accueil') }}</a><span aria-hidden="true">/</span><span>{{ __('Blog') }}</span>
                </nav>
                <h1>{{ __('Notes de terrain sur Laravel, la sécurité et le SaaS.') }}</h1>
                <p class="lead">{{ __('Ce que j’apprends en concevant des plateformes et en formant des développeurs, expliqué simplement.') }}</p>
            </div>
            <div class="tile tile-dark stat reveal is-visible" style="--delay: .08s">
                <i class="fas fa-newspaper watermark" aria-hidden="true"></i>
                <span class="tile-label"><i class="fas fa-pen-nib" aria-hidden="true"></i> {{ __('Articles') }}</span>
                <div>
                    <div class="stat-value"><span data-count="{{ $posts->total() }}">{{ $posts->total() }}</span></div>
                    <p>{{ __('articles publiés') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($tags->count() > 1)
                <nav class="filters reveal" aria-label="{{ __('Filtrer par thème') }}">
                    <a href="{{ lroute('blog.index') }}" @class(['is-active' => ! $activeTag])>{{ __('Tous') }}</a>
                    @foreach ($tags as $tag)
                        <a href="{{ lroute('blog.index', ['tag' => $tag]) }}" @class(['is-active' => $activeTag === $tag])>{{ $tag }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($posts->isEmpty())
                <div class="tile empty">{{ __('Les premiers articles arrivent bientôt.') }}</div>
            @else
                <div class="bento">
                    @foreach ($posts as $post)
                        <x-site.post-card :post="$post" :wide="$loop->first && $posts->currentPage() === 1" :dark="$loop->first && $posts->currentPage() === 1" :delay="($loop->index % 3) * 0.06" />
                    @endforeach
                </div>
                @if ($posts->hasPages())
                    <div class="pager-links">
                        @if ($posts->previousPageUrl())<a class="btn btn-ghost" href="{{ $posts->previousPageUrl() }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('Articles plus récents') }}</a>@endif
                        @if ($posts->nextPageUrl())<a class="btn btn-ghost" href="{{ $posts->nextPageUrl() }}">{{ __('Articles plus anciens') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>@endif
                    </div>
                @endif
            @endif
        </div>
    </section>
</x-layouts.site>
