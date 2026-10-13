{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('projects.index') }}</loc>
        @if ($lastProjectUpdate)<lastmod>{{ $lastProjectUpdate->toAtomString() }}</lastmod>@endif
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @foreach ($projects as $project)
        <url>
            <loc>{{ route('projects.show', $project) }}</loc>
            <lastmod>{{ $project->updated_at->toAtomString() }}</lastmod>
            <priority>0.7</priority>
        </url>
    @endforeach
</urlset>
