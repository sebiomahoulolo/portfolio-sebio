<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use Database\Seeders\PortfolioContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedSlugsTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $attributes = []): Post
    {
        return Post::create($attributes + [
            'title' => ['fr' => 'Sécuriser une application Laravel', 'en' => 'Securing a Laravel application'],
            'slug' => 'securiser-application-laravel',
            'body' => ['fr' => str_repeat('Contenu de l’article. ', 20), 'en' => str_repeat('Article content. ', 20)],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_english_slug_is_generated_from_the_english_title(): void
    {
        $this->assertSame('securing-a-laravel-application', $this->makePost()->slug_en);
    }

    public function test_each_language_has_its_own_address(): void
    {
        $this->makePost();

        $this->get('/blog/securiser-application-laravel')->assertOk()
            ->assertSee('hreflang="en" href="'.url('/en/blog/securing-a-laravel-application').'"', false);

        $this->get('/en/blog/securing-a-laravel-application')->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/en/blog/securing-a-laravel-application').'">', false)
            ->assertSee('hreflang="fr" href="'.url('/blog/securiser-application-laravel').'"', false);
    }

    public function test_old_english_address_redirects_permanently(): void
    {
        $this->makePost();

        $this->get('/en/blog/securiser-application-laravel')
            ->assertStatus(301)
            ->assertRedirect(url('/en/blog/securing-a-laravel-application'));

        // L'adresse anglaise sur la version française renvoie vers l'adresse française.
        $this->get('/blog/securing-a-laravel-application')->assertNotFound();
    }

    public function test_without_english_title_the_french_slug_is_kept(): void
    {
        $post = $this->makePost(['title' => ['fr' => 'Article seulement en français']]);

        $this->assertNull($post->slug_en);
        $this->get('/en/blog/securiser-application-laravel')->assertOk();
    }

    public function test_english_slug_does_not_change_when_the_title_is_edited(): void
    {
        $post = $this->makePost();
        $post->update(['title' => ['fr' => 'Nouveau titre', 'en' => 'A brand new title']]);

        $this->assertSame('securing-a-laravel-application', $post->fresh()->slug_en);
    }

    public function test_sitemap_and_services_use_translated_addresses(): void
    {
        $this->seed(PortfolioContentSeeder::class);
        $this->makePost();
        $service = Service::where('slug', 'developpement-web')->firstOrFail();

        $this->get('/sitemap.xml')
            ->assertSee('<loc>'.url('/en/blog/securing-a-laravel-application').'</loc>', false)
            ->assertSee('<loc>'.url('/blog/securiser-application-laravel').'</loc>', false)
            ->assertSee('<loc>'.url('/en/services/'.$service->slugFor('en')).'</loc>', false);

        $this->assertNotSame('developpement-web', $service->slugFor('en'));
        $this->get('/en/services')->assertSee(url('/en/services/'.$service->slugFor('en')), false);
    }
}
