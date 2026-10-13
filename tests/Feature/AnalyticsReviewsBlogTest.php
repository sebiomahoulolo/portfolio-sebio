<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Livewire\Admin\Analytics;
use App\Livewire\Admin\TestimonialIndex;
use App\Models\PageView;
use App\Models\Post;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\User;
use App\Notifications\NewReview;
use App\Livewire\Admin\Posts\PostForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AnalyticsReviewsBlogTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36';

    private function track(array $overrides = [], array $headers = [])
    {
        return $this->withHeaders(['User-Agent' => self::BROWSER] + $headers)
            ->post(route('analytics.store'), $overrides + ['path' => '/projets', 'title' => 'Projets', 'timezone' => 'Africa/Porto-Novo', 'locale' => 'fr']);
    }

    // ---------- Mesure d'audience ----------

    public function test_page_view_is_recorded_anonymously_with_country_from_timezone(): void
    {
        $this->track(['referrer' => 'https://www.linkedin.com/feed/'])->assertCreated()->assertJsonStructure(['id']);

        $view = PageView::sole();
        $this->assertSame('/projets', $view->path);
        $this->assertSame('BJ', $view->country_code);
        $this->assertSame('linkedin.com', $view->referrer_host);
        $this->assertSame('desktop', $view->device);
        $this->assertSame('Chrome', $view->browser);
        // Aucune adresse IP en clair.
        $this->assertStringNotContainsString('127.0.0.1', json_encode($view->getAttributes()));
    }

    public function test_bots_admins_and_do_not_track_are_not_counted(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])->post(route('analytics.store'), ['path' => '/'])->assertNoContent();
        $this->track([], ['DNT' => '1'])->assertNoContent();
        $this->actingAs(User::factory()->owner()->create());
        $this->track()->assertNoContent();

        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_duration_can_only_be_updated_by_the_same_session(): void
    {
        $response = $this->track();
        $id = $response->json('id');
        // Le navigateur renvoie le cookie de session reçu avec la page vue.
        $sessionCookie = $response->getCookie(config('session.cookie'), false);

        $this->withUnencryptedCookie($sessionCookie->getName(), $sessionCookie->getValue())
            ->withHeaders(['User-Agent' => self::BROWSER])
            ->post(route('analytics.duration', $id), ['duration' => 95])
            ->assertNoContent();
        $this->assertSame(95, PageView::sole()->duration_seconds);

        // Une mesure appartenant à une autre session ne peut pas être modifiée.
        $foreign = PageView::create(['uuid' => (string) str()->uuid(), 'visitor_hash' => 'x', 'session_hash' => 'autre-session', 'path' => '/']);
        $this->post(route('analytics.duration', $foreign->uuid), ['duration' => 9999])->assertNoContent();
        $this->assertSame(0, $foreign->fresh()->duration_seconds);
    }

    public function test_public_visitor_count_is_shown_in_the_footer(): void
    {
        PageView::create(['uuid' => (string) str()->uuid(), 'visitor_hash' => 'a', 'session_hash' => 's1', 'path' => '/']);
        PageView::create(['uuid' => (string) str()->uuid(), 'visitor_hash' => 'b', 'session_hash' => 's2', 'path' => '/']);

        $this->get('/')->assertSee('2 visiteurs');
        $this->get('/en')->assertSee('2 visitors');
    }

    public function test_only_the_owner_sees_statistics(): void
    {
        $this->actingAs(User::factory()->editor()->create())->get('/admin/statistiques')->assertForbidden();

        PageView::create(['uuid' => (string) str()->uuid(), 'visitor_hash' => 'a', 'session_hash' => 's1', 'path' => '/services', 'country_code' => 'BJ', 'duration_seconds' => 120]);

        Livewire::actingAs(User::factory()->owner()->create())
            ->test(Analytics::class)
            ->assertSee('/services')
            ->assertSee('Bénin')
            ->set('period', '7')
            ->assertOk();
    }

    // ---------- Avis des visiteurs ----------

    private function review(array $overrides = [])
    {
        return $this->from('/donner-mon-avis')->post(route('reviews.store'), $overrides + [
            'author_name' => 'Awa Dossou',
            'author_title' => 'Directrice, Agence Lumière',
            'email' => 'awa@example.com',
            'rating' => 5,
            'quote' => 'Travail rigoureux, livré dans les délais et très bien expliqué.',
            'consent' => '1',
            'locale' => 'fr',
            'website' => '',
        ]);
    }

    public function test_visitor_review_waits_for_approval(): void
    {
        Notification::fake();

        $this->review()->assertSessionHas('review_sent');

        $review = Testimonial::sole();
        $this->assertSame('pending', $review->status);
        $this->assertFalse($review->is_published);
        $this->assertSame('site', $review->source);
        Notification::assertSentOnDemand(NewReview::class);

        $this->get('/')->assertDontSee('Travail rigoureux');

        Livewire::actingAs(User::factory()->owner()->create())
            ->test(TestimonialIndex::class)
            ->call('approve', $review->id);

        $this->get('/')->assertSee('Travail rigoureux')->assertSee('Avis vérifié');
        // L'email reste privé.
        $this->get('/')->assertDontSee('awa@example.com');
    }

    public function test_review_requires_consent_and_blocks_bots(): void
    {
        Notification::fake();

        $this->review(['consent' => null])->assertSessionHasErrorsIn('review', ['consent']);
        $this->review(['website' => 'spam'])->assertSessionHasErrorsIn('review', ['website']);
        $this->review(['rating' => 7])->assertSessionHasErrorsIn('review', ['rating']);

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_english_review_is_displayed_on_the_french_site(): void
    {
        Notification::fake();
        $this->review(['locale' => 'en', 'quote' => 'Great collaboration, clear communication and solid code.']);
        Testimonial::sole()->forceFill(['status' => 'approved', 'is_published' => true])->save();

        $this->get('/')->assertSee('Great collaboration');
    }

    // ---------- Blog ----------

    public function test_published_posts_are_listed_and_drafts_hidden(): void
    {
        $published = Post::create(['slug' => 'article-public', 'title' => ['fr' => 'Article public', 'en' => 'Public article'], 'body' => ['fr' => str_repeat('Contenu. ', 40)], 'tags' => ['Laravel'], 'published_at' => now()->subDay()]);
        $published->forceFill(['status' => PublicationStatus::Published])->save();
        Post::create(['slug' => 'brouillon', 'title' => ['fr' => 'Brouillon secret'], 'body' => ['fr' => 'x']]);

        $this->get('/blog')->assertOk()->assertSee('Article public')->assertDontSee('Brouillon secret');
        $this->get('/en/blog')->assertSee('Public article');
        $this->get('/blog/article-public')->assertOk()->assertSee('min de lecture');
        $this->get('/blog/brouillon')->assertNotFound();
        $this->get('/blog?tag=Laravel')->assertSee('Article public');
        $this->get('/sitemap.xml')->assertSee(url('/blog/article-public'), false);
    }

    public function test_code_blocks_in_articles_are_escaped(): void
    {
        $post = Post::create(['slug' => 'code', 'title' => ['fr' => 'Code'], 'body' => ['fr' => "Texte\n\n```php\n<script>alert(1)</script>\n```"], 'published_at' => now()->subDay()]);
        $post->forceFill(['status' => PublicationStatus::Published])->save();

        $this->get('/blog/code')
            ->assertSee('class="code-block"', false)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_articles_can_show_images_and_videos_but_not_unsafe_links(): void
    {
        $body = implode("\n\n", [
            '![Tableau de bord](/storage/posts/media/2026/10/capture.webp)',
            '![](/storage/posts/media/2026/10/demo.mp4)',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            '![piège](javascript:alert(1))',
        ]);
        $post = Post::create(['slug' => 'medias', 'title' => ['fr' => 'Médias'], 'body' => ['fr' => $body], 'published_at' => now()->subDay()]);
        $post->forceFill(['status' => PublicationStatus::Published])->save();

        $this->get('/blog/medias')
            ->assertSee('<img src="/storage/posts/media/2026/10/capture.webp" alt="Tableau de bord"', false)
            ->assertSee('<figcaption>Tableau de bord</figcaption>', false)
            ->assertSee('<video src="/storage/posts/media/2026/10/demo.mp4"', false)
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertDontSee('src="javascript:', false);
    }

    public function test_uploaded_media_is_appended_to_the_article_body(): void
    {
        Storage::fake('public');
        $post = Post::create(['slug' => 'upload', 'title' => ['fr' => 'Upload'], 'body' => ['fr' => 'Intro', 'en' => '']]);

        $component = Livewire::actingAs(User::factory()->owner()->create())
            ->test(PostForm::class, ['post' => $post])
            ->set('media', [UploadedFile::fake()->image('capture.png'), UploadedFile::fake()->create('demo.mp4', 2000, 'video/mp4')])
            ->assertHasNoErrors();

        $body = $component->get('form.body');
        $this->assertStringStartsWith("Intro\n\n![](/storage/posts/media/", $body['fr']);
        $this->assertSame(2, substr_count($body['fr'], '![]('));
        $this->assertStringContainsString('.mp4)', $body['en']);
        $this->assertCount(2, Storage::disk('public')->allFiles('posts/media'));

        $component->set('media', [UploadedFile::fake()->create('virus.exe', 10)])->assertHasErrors('media.0');
    }

    // ---------- Études de cas ----------

    public function test_case_study_sections_and_metrics_are_displayed(): void
    {
        $project = Project::factory()->published()->create([
            'slug' => 'etude',
            'challenge' => ['fr' => 'Les candidatures se perdaient par email.'],
            'solution' => ['fr' => "- Espace candidat\n- Suivi des candidatures"],
            'metrics' => [['value' => '3 000+', 'label' => ['fr' => 'candidats', 'en' => 'candidates']]],
        ]);

        $this->get('/projets/etude')
            ->assertSee('Étude de cas')
            ->assertSee('Les candidatures se perdaient par email.')
            ->assertSee('<li>Espace candidat</li>', false)
            ->assertSee('3 000+')
            ->assertDontSee('Contexte'); // section vide masquée

        $this->get('/en/projects/etude')->assertSee('candidates')->assertSee('Case study');
    }
}
