<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /**
     * Seules les pages publiques, publiées et indexables y figurent, dans
     * chaque langue, avec leurs équivalents (hreflang) et leur date de mise à jour.
     */
    public function __invoke(): Response
    {
        $posts = Post::published()->where('is_indexable', true)->latest('published_at')->get(['slug', 'slug_en', 'title', 'updated_at']);
        $services = Service::published()->orderBy('sort_order')->get(['slug', 'slug_en', 'title', 'updated_at']);
        $projects = Project::published()->where('is_indexable', true)->orderBy('sort_order')->get(['slug', 'slug_en', 'title', 'updated_at']);

        $profileUpdated = ProfileSetting::query()->max('updated_at');
        $homeUpdated = collect([$profileUpdated, $projects->max('updated_at'), $services->max('updated_at')])
            ->filter()->map(fn ($date) => Carbon::parse($date))->max();

        $pages = collect([
            ['home', [], 1.0, $homeUpdated],
            ['projects.index', [], 0.8, $projects->max('updated_at')],
            ['services.index', [], 0.8, $services->max('updated_at')],
            ['blog.index', [], 0.7, $posts->max('updated_at')],
            ['legal.notice', [], 0.2, LegalController::UPDATED_AT],
            ['legal.privacy', [], 0.2, LegalController::UPDATED_AT],
        ]);

        $posts->each(fn ($post) => $pages->push(['blog.show', $post->localizedRouteParameters(), 0.6, $post->updated_at]));
        $services->each(fn ($service) => $pages->push(['services.show', $service->localizedRouteParameters(), 0.7, $service->updated_at]));
        $projects->each(fn ($project) => $pages->push(['projects.show', $project->localizedRouteParameters(), 0.7, $project->updated_at]));

        return response()
            ->view('site.sitemap', [
                'pages' => $pages,
                'locales' => array_keys(config('portfolio.locales')),
                'defaultLocale' => config('portfolio.default_locale'),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * robots.txt généré : l'adresse du sitemap suit toujours le vrai domaine (APP_URL).
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /livewire/',
            'Disallow: /t$',
            'Disallow: /t/',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * llms.txt : présentation courte et liens clés, lisibles par les assistants IA.
     */
    public function llms(): Response
    {
        $profile = ProfileSetting::current();
        $link = fn (string $label, string $url, ?string $text = null) => "- [{$label}]({$url})".($text ? ': '.Str::squish(strip_tags($text)) : '');

        $lines = [
            '# '.$profile->name,
            '',
            '> '.Str::squish(strip_tags($profile->translate('seo_description') ?: $profile->translate('intro') ?: $profile->translate('headline'))),
            '',
            implode(' · ', array_filter([$profile->translate('headline'), $profile->location, $profile->email])),
            '',
            '## Services',
            ...Service::published()->orderBy('sort_order')->get()
                ->map(fn ($s) => $link($s->translate('title'), lroute('services.show', ['slug' => $s->slug], 'fr'), $s->translate('summary'))),
            '',
            '## Projets',
            ...Project::published()->where('is_indexable', true)->orderBy('sort_order')->get()
                ->map(fn ($p) => $link($p->translate('title'), lroute('projects.show', ['slug' => $p->slug], 'fr'), $p->translate('excerpt'))),
            '',
            '## Articles',
            ...Post::published()->where('is_indexable', true)->latest('published_at')->get()
                ->map(fn ($p) => $link($p->translate('title'), lroute('blog.show', ['slug' => $p->slug], 'fr'), $p->translate('excerpt'))),
            '',
            '## Liens',
            $link('Accueil (français)', lroute('home', [], 'fr')),
            $link('Home (English)', lroute('home', [], 'en')),
            $link('Contact', lroute('home', [], 'fr').'#contact'),
            ...collect($profile->social_links ?? [])->filter()->map(fn ($url, $network) => $link(Str::ucfirst($network), $url))->values(),
        ];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
