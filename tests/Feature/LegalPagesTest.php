<?php

namespace Tests\Feature;

use Database\Seeders\PortfolioContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PortfolioContentSeeder::class);
    }

    public function test_legal_pages_exist_in_both_languages(): void
    {
        $this->get('/mentions-legales')->assertOk()->assertSee('Mentions légales')->assertSee('Hostinger International Ltd')->assertSee('contact@mahoulolo-sebio.com');
        $this->get('/confidentialite')->assertOk()->assertSee('Politique de confidentialité')->assertSee('Vos droits')->assertSee('3 ans');
        $this->get('/en/legal-notice')->assertOk()->assertSee('Legal notice')->assertSee('Publisher');
        $this->get('/en/privacy')->assertOk()->assertSee('Privacy policy')->assertSee('Your rights');
    }

    public function test_language_switch_points_to_the_translated_legal_page(): void
    {
        $this->get('/confidentialite')->assertSee('hreflang="en" href="'.url('/en/privacy').'"', false);
    }

    public function test_footer_and_forms_link_to_the_privacy_policy(): void
    {
        $this->get('/')
            ->assertSee(url('/mentions-legales'), false)
            ->assertSee(url('/confidentialite'), false);

        $this->get('/donner-mon-avis')->assertSee(url('/confidentialite'), false);
    }

    public function test_legal_pages_are_in_the_sitemap(): void
    {
        $this->get('/sitemap.xml')
            ->assertSee(url('/mentions-legales'), false)
            ->assertSee(url('/en/privacy'), false);
    }
}
