<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent_on_every_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeaderMissing('X-Powered-By');

        $this->get('/admin/connexion')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_admin_pages_are_never_cached(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->assertStringContainsString('no-store', $this->get('/admin')->headers->get('Cache-Control'));
    }

    public function test_favicon_is_present_on_public_admin_and_auth_pages(): void
    {
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));

        $this->get('/')->assertSee('favicon.ico', false)->assertSee('apple-touch-icon.png', false);
        $this->get('/admin/connexion')->assertSee('favicon.ico', false);
        $this->actingAs(User::factory()->create())->get('/admin')->assertSee('favicon.ico', false);
    }

    public function test_public_site_includes_content_protection_notice(): void
    {
        $this->get('/')->assertSee('protect-toast', false)->assertSee('Contenu protégé');
        $this->get('/en')->assertSee('Protected content');
    }

    public function test_error_pages_are_branded_and_translated(): void
    {
        $this->get('/page-qui-nexiste-pas')
            ->assertNotFound()
            ->assertSee('Page introuvable')
            ->assertSee('favicon.ico', false);

        $this->get('/en/missing-page')->assertNotFound()->assertSee('Page not found');
    }
}
