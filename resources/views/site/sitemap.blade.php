{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach ($pages as [$name, $parameters, $priority])
        @foreach ($locales as $locale)
            <url>
                <loc>{{ lroute($name, $parameters, $locale) }}</loc>
                @foreach ($locales as $alternate)
                    <xhtml:link rel="alternate" hreflang="{{ $alternate }}" href="{{ lroute($name, $parameters, $alternate) }}"/>
                @endforeach
                <priority>{{ number_format($priority, 1) }}</priority>
            </url>
        @endforeach
    @endforeach
</urlset>
