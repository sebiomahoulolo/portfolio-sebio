{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    {{-- $parameters : communs à toutes les langues, ou par langue pour les adresses traduites. --}}
    @foreach ($pages as [$name, $parameters, $priority, $updated])
        @foreach ($locales as $locale)
            <url>
                <loc>{{ lroute($name, $parameters[$locale] ?? $parameters, $locale) }}</loc>
                @foreach ($locales as $alternate)
                    <xhtml:link rel="alternate" hreflang="{{ $alternate }}" href="{{ lroute($name, $parameters[$alternate] ?? $parameters, $alternate) }}"/>
                @endforeach
                <xhtml:link rel="alternate" hreflang="x-default" href="{{ lroute($name, $parameters[$defaultLocale] ?? $parameters, $defaultLocale) }}"/>
                @if ($updated)
                    <lastmod>{{ \Illuminate\Support\Carbon::parse($updated)->toAtomString() }}</lastmod>
                @endif
                <priority>{{ number_format($priority, 1) }}</priority>
            </url>
        @endforeach
    @endforeach
</urlset>
