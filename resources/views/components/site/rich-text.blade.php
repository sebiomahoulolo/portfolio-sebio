@props(['text'])

@php
    /*
     * Mise en forme simple et sûre d'un texte saisi dans l'administration :
     * « ## » en début de ligne crée un intertitre, « - » ou « * » un élément
     * de liste, une ligne vide sépare les paragraphes. Tout est échappé.
     */
    $blocks = [];
    $paragraph = [];
    $list = [];

    $flush = function () use (&$blocks, &$paragraph, &$list) {
        if ($paragraph) {
            $blocks[] = ['p', $paragraph];
            $paragraph = [];
        }
        if ($list) {
            $blocks[] = ['ul', $list];
            $list = [];
        }
    };

    foreach (preg_split('/\R/', trim((string) $text)) as $line) {
        $line = trim($line);

        if ($line === '') {
            $flush();
        } elseif (str_starts_with($line, '## ')) {
            $flush();
            $blocks[] = ['h2', substr($line, 3)];
        } elseif (str_starts_with($line, '- ') || str_starts_with($line, '* ')) {
            if ($paragraph) {
                $blocks[] = ['p', $paragraph];
                $paragraph = [];
            }
            $list[] = substr($line, 2);
        } else {
            if ($list) {
                $blocks[] = ['ul', $list];
                $list = [];
            }
            $paragraph[] = $line;
        }
    }
    $flush();
@endphp

<div {{ $attributes->class('prose') }}>
    @foreach ($blocks as [$type, $content])
        @if ($type === 'h2')
            <h2>{{ $content }}</h2>
        @elseif ($type === 'ul')
            <ul>
                @foreach ($content as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @else
            <p>{!! implode('<br>', array_map('e', $content)) !!}</p>
        @endif
    @endforeach
</div>
