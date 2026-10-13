@props(['text'])

@php
    /*
     * Mise en forme simple et sûre d'un texte saisi dans l'administration.
     * Tout est échappé, puis seuls quelques motifs sont interprétés :
     *   ## intertitre · ### sous-titre · - ou * liste · > citation
     *   ``` bloc de code ``` · `code` · **gras**
     */
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
            @default
                <p>{!! implode('<br>', array_map($inline, $block[1])) !!}</p>
        @endswitch
    @endforeach
</div>
