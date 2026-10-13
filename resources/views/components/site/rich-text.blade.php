@props(['text'])

@php
    /*
     * Mise en forme simple et sûre d'un texte saisi dans l'administration.
     * Tout est échappé, puis seuls quelques motifs sont interprétés :
     *   ## intertitre · ### sous-titre · - ou * liste · > citation
     *   ``` bloc de code ``` · `code` · **gras**
     *   ![légende](adresse) : image, ou vidéo si l'adresse finit par .mp4/.webm
     *   lien YouTube ou Vimeo seul sur sa ligne : vidéo intégrée
     */
    // Seules les adresses https:// ou internes au site (/storage/...) sont acceptées.
    $safeUrl = fn (string $url) => (bool) preg_match('~^(https://|/(?!/))~i', $url);
    $media = function (string $line) use ($safeUrl): ?array {
        if (preg_match('/^!\[([^\]]*)\]\((\S+)\)$/', $line, $m) && $safeUrl($m[2])) {
            return [preg_match('/\.(mp4|webm)(\?\S*)?$/i', $m[2]) ? 'video' : 'img', $m[2], $m[1]];
        }
        if (preg_match('~^https://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([\w-]{11})\S*$~', $line, $m)) {
            return ['embed', 'https://www.youtube-nocookie.com/embed/'.$m[1], 'YouTube'];
        }
        if (preg_match('~^https://(?:www\.)?vimeo\.com/(\d+)\S*$~', $line, $m)) {
            return ['embed', 'https://player.vimeo.com/video/'.$m[1], 'Vimeo'];
        }

        return null;
    };
    $inline = fn (string $line) => preg_replace(
        ['/`([^`]+)`/', '/\*\*([^*]+)\*\*/'],
        ['<code>$1</code>', '<strong>$1</strong>'],
        e($line),
    );

    $blocks = [];
    $paragraph = [];
    $list = [];
    $quote = [];
    $code = null;

    $flush = function () use (&$blocks, &$paragraph, &$list, &$quote) {
        if ($paragraph) { $blocks[] = ['p', $paragraph]; $paragraph = []; }
        if ($list) { $blocks[] = ['ul', $list]; $list = []; }
        if ($quote) { $blocks[] = ['quote', $quote]; $quote = []; }
    };

    foreach (preg_split('/\R/', trim((string) $text)) as $raw) {
        if ($code !== null) {
            if (str_starts_with(trim($raw), '```')) {
                $blocks[] = ['code', $code['lines'], $code['lang']];
                $code = null;
            } else {
                $code['lines'][] = $raw;
            }
            continue;
        }

        $line = trim($raw);

        if (str_starts_with($line, '```')) {
            $flush();
            $code = ['lang' => trim(substr($line, 3)), 'lines' => []];
        } elseif ($line === '') {
            $flush();
        } elseif ($item = $media($line)) {
            $flush();
            $blocks[] = $item;
        } elseif (str_starts_with($line, '### ')) {
            $flush();
            $blocks[] = ['h3', substr($line, 4)];
        } elseif (str_starts_with($line, '## ')) {
            $flush();
            $blocks[] = ['h2', substr($line, 3)];
        } elseif (str_starts_with($line, '> ')) {
            if ($paragraph || $list) { $flush(); }
            $quote[] = substr($line, 2);
        } elseif (str_starts_with($line, '- ') || str_starts_with($line, '* ')) {
            if ($paragraph || $quote) { $blocks[] = $paragraph ? ['p', $paragraph] : ['quote', $quote]; $paragraph = []; $quote = []; }
            $list[] = substr($line, 2);
        } else {
            if ($list || $quote) { $flush(); }
            $paragraph[] = $line;
        }
    }
    if ($code !== null) {
        $blocks[] = ['code', $code['lines'], $code['lang']];
    }
    $flush();
@endphp

<div {{ $attributes->class('prose') }}>
    @foreach ($blocks as $block)
        @switch($block[0])
            @case('h2')
                <h2>{!! $inline($block[1]) !!}</h2>
                @break
            @case('h3')
                <h3>{!! $inline($block[1]) !!}</h3>
                @break
            @case('ul')
                <ul>@foreach ($block[1] as $item)<li>{!! $inline($item) !!}</li>@endforeach</ul>
                @break
            @case('quote')
                <blockquote>{!! implode('<br>', array_map($inline, $block[1])) !!}</blockquote>
                @break
            @case('code')
                <pre class="code-block" @if ($block[2]) data-lang="{{ $block[2] }}" @endif><code>{{ implode("\n", $block[1]) }}</code></pre>
                @break
            @case('img')
                <figure class="prose-media">
                    <img src="{{ $block[1] }}" alt="{{ $block[2] }}" loading="lazy">
                    @if ($block[2])<figcaption>{{ $block[2] }}</figcaption>@endif
                </figure>
                @break
            @case('video')
                <figure class="prose-media">
                    <video src="{{ $block[1] }}" controls controlslist="nodownload" preload="metadata" playsinline oncontextmenu="return false;"></video>
                    @if ($block[2])<figcaption>{{ $block[2] }}</figcaption>@endif
                </figure>
                @break
            @case('embed')
                <figure class="prose-media prose-embed">
                    <iframe src="{{ $block[1] }}" title="{{ __('Vidéo') }} {{ $block[2] }}" loading="lazy" allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </figure>
                @break
            @default
                <p>{!! implode('<br>', array_map($inline, $block[1])) !!}</p>
        @endswitch
    @endforeach
</div>
