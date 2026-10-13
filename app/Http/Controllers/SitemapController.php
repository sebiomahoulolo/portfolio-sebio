<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Seules les pages publiques, publiées et indexables y figurent, dans
     * chaque langue, avec leurs équivalents (hreflang).
     */
    public function __invoke(): Response
    {
        $pages = collect([
            ['home', [], 1.0],
            ['projects.index', [], 0.8],
            ['services.index', [], 0.8],
            ['blog.index', [], 0.7],
        ]);

        Post::published()->where('is_indexable', true)->latest('published_at')->get(['slug'])
            ->each(fn ($post) => $pages->push(['blog.show', ['slug' => $post->slug], 0.6]));

        Service::published()->orderBy('sort_order')->get(['slug'])
            ->each(fn ($service) => $pages->push(['services.show', ['slug' => $service->slug], 0.7]));

        Project::published()->where('is_indexable', true)->orderBy('sort_order')->get(['slug'])
            ->each(fn ($project) => $pages->push(['projects.show', ['slug' => $project->slug], 0.7]));

        return response()
            ->view('site.sitemap', ['pages' => $pages, 'locales' => array_keys(config('portfolio.locales'))])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
