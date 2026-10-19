<?php

namespace Tests\Feature;

use Database\Seeders\BlogAndCaseStudiesSeeder;
use Database\Seeders\PortfolioContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PortfolioContentSeeder::class, BlogAndCaseStudiesSeeder::class]);
    }

    public function test_robots_txt_points_to_the_sitemap_of_the_current_domain(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }

    public function test_sitemap_has_lastmod_and_x_default(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<lastmod>', false)
            ->assertSee('hreflang="x-default"', false);
    }

    public function test_llms_txt_summarises_the_site(): void
    {
        $this->get('/llms.txt')
            ->assertOk()
            ->assertSee('# Mahoulolo')
            ->assertSee('## Services')
            ->assertSee('## Projets');
    }

    public function test_home_declares_website_schema_and_social_tags(): void
    {
        $this->get('/')
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('twitter:title', false)
            ->assertSee('og:locale:alternate', false);
    }

    public function test_structured_data_is_valid_json(): void
    {
        foreach (['/', '/services', '/projets/stagesbenin'] as $url) {
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->get($url)->getContent(), $blocks);

            $this->assertNotEmpty($blocks[1], $url);
            foreach ($blocks[1] as $json) {
                // Blade prenait « @context » pour une directive et cassait le JSON.
                $this->assertSame('https://schema.org', json_decode($json, true)['@context'] ?? null, $url);
            }
        }
    }

    public function test_titles_and_descriptions_fit_google_limits(): void
    {
        foreach (['/', '/en', '/services', '/en/services', '/projets', '/blog', '/projets/stagesbenin'] as $url) {
            $html = $this->get($url)->getContent();
            preg_match('#<title>(.*?)</title>#s', $html, $title);
            preg_match('#<meta name="description" content="([^"]*)"#', $html, $description);

            $this->assertLessThanOrEqual(65, mb_strlen(html_entity_decode($title[1])), $url);
            $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($description[1])), $url);
            $this->assertGreaterThanOrEqual(70, mb_strlen(html_entity_decode($description[1])), $url);
        }

        // Titres distincts en français et en anglais.
        $this->get('/services')->assertSee('<title>Services de développement web et SaaS', false);
        $this->get('/en/services')->assertSee('<title>Web &amp; SaaS development services', false);
    }

    public function test_short_descriptions_are_completed_and_long_ones_cut_between_words(): void
    {
        $this->assertSame('Sites vitrines. Contexte en plus.', meta_description('Sites vitrines.', 'Contexte en plus.'));

        $long = meta_description(str_repeat('mot ', 60));
        $this->assertLessThanOrEqual(160, mb_strlen($long));
        $this->assertStringEndsWith('mot…', $long);
    }

    public function test_google_analytics_waits_for_consent(): void
    {
        $this->get('/')->assertDontSee('consent-banner', false);

        config(['portfolio.seo.google_analytics' => 'G-TEST123']);

        // Le script Google n'est jamais dans la page : site.js le charge après « Accepter ».
        $this->get('/')
            ->assertSee('data-ga="G-TEST123"', false)
            ->assertSee('data-consent-open', false)
            ->assertDontSee('googletagmanager.com', false);
        $this->get('/confidentialite')->assertSee('Google Analytics (seulement avec votre accord)');
        $this->get('/en/privacy')->assertSee('Google Analytics (only with your consent)');
    }

    public function test_bare_domain_redirects_to_www(): void
    {
        config(['app.url' => 'https://www.example.com']);

        $this->get('https://example.com/projets?x=1')
            ->assertStatus(301)
            ->assertRedirect('https://www.example.com/projets?x=1');

        $this->get('https://www.example.com/')->assertOk();
    }

    public function test_detail_pages_declare_breadcrumbs(): void
    {
        $this->get('/services')->assertSee('"@type":"BreadcrumbList"', false);
        $this->get('/projets/stagesbenin')->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_review_form_is_not_indexed(): void
    {
        $this->get('/donner-mon-avis')->assertSee('noindex', false);
    }

    public function test_webmaster_verification_tags_are_rendered_when_configured(): void
    {
        $this->get('/')->assertDontSee('google-site-verification', false);

        config(['portfolio.seo.google_verification' => 'abc123', 'portfolio.seo.bing_verification' => 'xyz789']);

        $this->get('/')
            ->assertSee('<meta name="google-site-verification" content="abc123">', false)
            ->assertSee('<meta name="msvalidate.01" content="xyz789">', false);
    }
}
