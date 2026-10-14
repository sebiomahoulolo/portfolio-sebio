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
