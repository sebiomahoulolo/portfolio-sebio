<?php

namespace Tests\Feature;

use App\Livewire\Admin\ProfileEditor;
use App\Models\Experience;
use App\Models\ProfileSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ExperienceYearsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function career(string $start = '2023-07-01'): void
    {
        Experience::create(['title' => ['fr' => 'Développeur'], 'company' => 'AfriCode', 'started_at' => $start, 'is_visible' => true]);
    }

    public function test_years_are_counted_from_the_first_job_and_ignore_a_wrong_manual_value(): void
    {
        Carbon::setTestNow('2026-10-14');
        ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév'], 'years_experience' => 4]);
        $this->career();

        // Juillet 2023 → octobre 2026 : 3 ans révolus, pas 4.
        $this->get('/')
            ->assertSee('<span data-count="3">3</span>+', false)
            ->assertSee('depuis juillet 2023');

        $this->get('/en')->assertSee('since July 2023');
    }

    public function test_the_number_grows_on_its_own_each_year(): void
    {
        Carbon::setTestNow('2027-07-01');
        ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév']]);
        $this->career();

        $this->get('/')->assertSee('<span data-count="4">4</span>+', false);
    }

    public function test_manual_value_is_used_only_without_career(): void
    {
        ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév'], 'years_experience' => 5]);

        $this->get('/')->assertSee('<span data-count="5">5</span>+', false);
    }

    public function test_placeholder_in_texts_follows_the_calculation(): void
    {
        Carbon::setTestNow('2026-10-14');
        ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév'], 'intro' => [
            'fr' => 'Développeur depuis plus de {ans} ans.',
            'en' => 'A developer for over {ans} years.',
        ]]);
        $this->career();

        $this->get('/')->assertSee('Développeur depuis plus de 3 ans.')->assertDontSee('{ans}');
        $this->get('/en')->assertSee('A developer for over 3 years.');

        // L'administration garde le marqueur, pour qu'il continue d'évoluer.
        $this->assertSame('Développeur depuis plus de {ans} ans.', ProfileSetting::first()->intro['fr']);
    }

    public function test_saving_the_profile_converts_typed_years_to_the_placeholder(): void
    {
        $this->actingAs(User::factory()->owner()->create());
        ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév'], 'email' => 'test@example.com']);

        Livewire::test(ProfileEditor::class)
            ->set('form.intro.fr', 'Développeur depuis plus de 2 ans.')
            ->set('form.intro.en', 'A developer for over 2 years.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['fr' => 'Développeur depuis plus de {ans} ans.', 'en' => 'A developer for over {ans} years.'], ProfileSetting::first()->intro);
    }

    public function test_uploaded_images_follow_the_address_in_use(): void
    {
        config(['app.url' => 'http://127.0.0.1:8123']);
        ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév'], 'avatar_path' => 'profile/photo.jpg']);

        // Site ouvert sur une autre adresse qu'APP_URL : l'image suit l'adresse réelle.
        $this->get('http://localhost:8007/')
            ->assertSee('http://localhost:8007/storage/profile/photo.jpg', false)
            ->assertDontSee('127.0.0.1:8123/storage', false);
    }

    public function test_migration_converts_hardcoded_years(): void
    {
        $profile = ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév'], 'intro' => [
            'fr' => 'Développeur Full Stack Laravel depuis plus de 2 ans, je conçois des applications.',
            'en' => 'A Full Stack Laravel developer for over 2 years, I design web applications.',
        ]]);

        (require database_path('migrations/2026_10_14_000004_use_years_placeholder_in_profile_texts.php'))->up();

        $this->assertSame([
            'fr' => 'Développeur Full Stack Laravel depuis plus de {ans} ans, je conçois des applications.',
            'en' => 'A Full Stack Laravel developer for over {ans} years, I design web applications.',
        ], $profile->fresh()->intro);
        $this->assertSame(1, DB::table('profile_settings')->count());
    }
}
