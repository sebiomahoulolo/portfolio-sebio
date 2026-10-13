@props(['project', 'delay' => 0, 'wide' => false])

@php
    $title = $project->translate('title');
@endphp

<article @class(['tile project-tile reveal', 'span-2' => $wide]) style="--delay: {{ $delay }}s">
    <div class="project-media">
        @if ($project->coverUrl())
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->translate('cover_alt') ?? '' }}" loading="lazy" width="800" height="500">
        @else
            <div class="project-placeholder" aria-hidden="true">
                <div class="browser">
                    <div class="browser-bar">
                        <i></i><i></i><i></i>
                        <span>{{ $project->demo_url ? parse_url($project->demo_url, PHP_URL_HOST) : Str::slug($title).'.app' }}</span>
                    </div>
                    <div class="browser-body">
                        <b>{{ $title }}</b>
                        <em></em><em></em><em class="short"></em>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="project-body">
        @if ($project->category || $project->period)
            <div class="project-meta">{{ collect([$project->category, $project->period])->filter()->join(' · ') }}</div>
        @endif
        <h3><a href="{{ route('projects.show', $project) }}">{{ $title }}</a></h3>
        <p>{{ $project->translate('excerpt') }}</p>
        @if ($project->technologies->isNotEmpty())
            <div class="tags">
                @foreach ($project->technologies as $tech)
                    <span class="tag">{{ $tech->name }}</span>
                @endforeach
            </div>
        @endif
    </div>
    <div class="project-links">
        <a href="{{ route('projects.show', $project) }}">Découvrir <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        @if ($project->demo_url)
            <a href="{{ $project->demo_url }}" target="_blank" rel="noopener">Site <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
        @endif
    </div>
</article>
