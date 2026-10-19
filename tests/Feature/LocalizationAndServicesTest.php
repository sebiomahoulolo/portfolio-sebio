<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use Database\Seeders\PortfolioContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LocalizationAndServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PortfolioContentSeeder::class);
    }

    public function test_home_page_is_available_in_french_and_english(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="fr"', false)
            ->assertSee('Je bâtis des SaaS sécurisés.')
            ->assertSee('hreflang="en" href="'.url('/en').'"', false);

        $this->get('/en')
            ->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertSee('I build secure SaaS.')
            ->assertSee('Explore my services')
            ->assertSee('hreflang="fr" href="'.url('/').'"', false);
    }

    public function test_translated_urls_point_to_each_other(): void
    {
        $this->get('/en/projects/africode')
            ->assertOk()
            ->assertSee('A 100% Laravel educational platform')
            ->assertSee(url('/projets/africode'), false);

        $this->get('/projets/africode')->assertOk()->assertSee(url('/en/projects/africode'), false);
    }

    public function test_missing_english_translation_falls_back_to_french(): void
    {
        Project::factory()->published()->create([
            'slug' => 'projet-fr',
            'title' => ['fr' => 'Projet uniquement en français'],
        ]);

        $this->get('/en/projects/projet-fr')->assertOk()->assertSee('Projet uniquement en français');
    }

    public function test_services_have_an_index_and_a_page_each(): void
    {
        $this->get('/services')->assertOk()->assertSee('Logiciels sur mesure &amp; SaaS', false)->assertSee('Écoute et cadrage');
        $this->get('/en/services')->assertOk()->assertSee('Custom software &amp; SaaS', false)->assertSee('Discovery &amp; scoping', false);

        $this->get('/services/logiciels-sur-mesure-saas')
            ->assertOk()
            ->assertSee('Architecture multi-tenant')
            // Le formulaire est pré-rempli pour une demande de devis sur ce service.
            ->assertSee('value="Demande de devis : Logiciels sur mesure &amp; SaaS"', false);
    }

    public function test_unpublished_service_is_hidden(): void
    {
        Service::where('slug', 'formation')->update(['is_published' => false]);

        $this->get('/services/formation')->assertNotFound();
        $this->get('/services')->assertDontSee('Formations Laravel et développement web');
    }

    public function test_contact_confirmation_follows_the_page_language(): void
    {
        Notification::fake();

        $this->from('/en')->post(route('contact.store'), [
            'type' => 'contact', 'name' => 'Jane Doe', 'email' => 'jane@example.com',
            'subject' => 'Hello', 'message' => 'I would like to discuss a SaaS project with you.',
            'website' => '', 'locale' => 'en',
        ])->assertSessionHas('contact_sent', 'Thank you Jane! Your message has been received. I\'ll get back to you within 2 business days.');
    }

    public function test_sitemap_contains_both_languages_with_alternates(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(url('/en/services/training'), false)
            ->assertSee(url('/services/formation'), false)
            ->assertSee('hreflang="en"', false);
    }

    public function test_layout_offers_theme_toggle_and_language_switch(): void
    {
        $this->get('/')
            ->assertSee('data-theme-toggle', false)
            ->assertSee('class="lang-switch"', false)
            ->assertSee("localStorage.getItem('theme')", false);
    }
}
