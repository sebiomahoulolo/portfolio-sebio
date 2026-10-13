<?php

namespace Tests\Feature;

use App\Livewire\Admin\CareerIndex;
use App\Livewire\Admin\ProfileEditor;
use App\Livewire\Admin\Services\ServiceForm;
use App\Livewire\Admin\Services\ServiceIndex;
use App\Livewire\Admin\TestimonialIndex;
use App\Models\Experience;
use App\Models\ProfileSetting;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\PortfolioContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->owner()->create();
    }

    public function test_owner_can_create_a_bilingual_service(): void
    {
        Livewire::actingAs($this->owner)
            ->test(ServiceForm::class)
            ->set('form.title.fr', 'Audit de performance')
            ->set('form.title.en', 'Performance audit')
            ->set('form.summary.fr', 'Votre site plus rapide.')
            ->set('features.fr', "Analyse Lighthouse\nOptimisation des requêtes\n\n")
            ->call('save')
            ->assertHasNoErrors();

        $service = Service::firstWhere('slug', 'audit-de-performance');
        $this->assertSame('Performance audit', $service->translate('title', 'en'));
        $this->assertSame(['Analyse Lighthouse', 'Optimisation des requêtes'], $service->featuresFor('fr'));

        $this->get('/en/services/audit-de-performance')->assertOk()->assertSee('Performance audit');
    }

    public function test_service_icon_must_be_a_font_awesome_class(): void
    {
        Livewire::actingAs($this->owner)
            ->test(ServiceForm::class)
            ->set('form.title.fr', 'Test')
            ->set('form.icon', '"><script>alert(1)</script>')
            ->call('save')
            ->assertHasErrors('form.icon');
    }

    public function test_services_can_be_hidden_and_reordered(): void
    {
        $this->seed(PortfolioContentSeeder::class);
        [$first, $second] = Service::orderBy('sort_order')->take(2)->get();

        Livewire::actingAs($this->owner)
            ->test(ServiceIndex::class)
            ->call('move', $second->id, 'up')
            ->call('togglePublished', $first->id);

        $this->assertTrue($second->fresh()->sort_order < $first->fresh()->sort_order);
        $this->assertFalse($first->fresh()->is_published);
    }

    public function test_editor_cannot_delete_a_service(): void
    {
        $this->seed(PortfolioContentSeeder::class);
        $service = Service::first();

        Livewire::actingAs(User::factory()->editor()->create())
            ->test(ServiceIndex::class)
            ->call('delete', $service->id)
            ->assertForbidden();

        $this->assertModelExists($service);
    }

    public function test_testimonial_can_be_added(): void
    {
        Livewire::actingAs($this->owner)
            ->test(TestimonialIndex::class)
            ->call('create')
            ->set('form.author_name', 'Awa Dossou')
            ->set('form.quote.fr', 'Excellent travail, livré dans les délais.')
            ->set('form.quote.en', 'Excellent work, delivered on time.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Excellent work, delivered on time.', Testimonial::sole()->translate('quote', 'en'));
        $this->get('/')->assertSee('Excellent travail, livré dans les délais.');
    }

    public function test_current_experience_has_no_end_date(): void
    {
        Livewire::actingAs($this->owner)
            ->test(CareerIndex::class)
            ->call('create', 'experience')
            ->set('form.title.fr', 'Lead développeur')
            ->set('form.company', 'AfriCode')
            ->set('form.started_at', '2026-01-01')
            ->set('form.ended_at', '2026-06-01')
            ->set('form.is_current', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(Experience::sole()->ended_at);
    }

    public function test_experience_end_date_cannot_precede_start(): void
    {
        Livewire::actingAs($this->owner)
            ->test(CareerIndex::class)
            ->call('create', 'experience')
            ->set('form.title.fr', 'Poste')
            ->set('form.company', 'Entreprise')
            ->set('form.started_at', '2026-01-01')
            ->set('form.ended_at', '2025-01-01')
            ->call('save')
            ->assertHasErrors('form.ended_at');
    }

    public function test_profile_tagline_and_expertise_are_saved_per_language(): void
    {
        $this->seed(PortfolioContentSeeder::class);

        Livewire::actingAs($this->owner)
            ->test(ProfileEditor::class)
            ->set('form.tagline.en', 'I ship secure platforms.')
            ->set('expertise.en', 'Laravel, Security , ')
            ->set('form.years_experience', 3)
            ->call('save')
            ->assertHasNoErrors();

        $profile = ProfileSetting::current();
        $this->assertSame(['Laravel', 'Security'], $profile->expertiseFor('en'));
        $this->assertSame(3, $profile->years_experience);
        $this->get('/en')->assertSee('I ship secure platforms.')->assertSee('data-count="3"', false);
    }
}
