<?php

namespace Tests\Feature;

use App\Livewire\Admin\ProfileEditor;
use App\Livewire\Admin\Technologies\TechnologyIndex;
use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SkillsAndLanguagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_skills_show_levels_instead_of_percentages(): void
    {
        Technology::create(['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'backend', 'proficiency' => 'expert']);
        Technology::create(['name' => 'Angular', 'slug' => 'angular', 'category' => 'frontend', 'proficiency' => 'notions']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Expert')
            ->assertSee('Notions')
            ->assertDontSee('skill-bar', false)
            ->assertDontSee(' %</span>', false);

        $this->get('/en')->assertSee('Expert')->assertSee('Basic');
    }

    public function test_project_count_appears_only_from_two_published_projects(): void
    {
        $laravel = Technology::create(['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'backend', 'proficiency' => 'expert']);
        $react = Technology::create(['name' => 'React', 'slug' => 'react', 'category' => 'frontend', 'proficiency' => 'confirmed']);

        $laravel->projects()->attach(Project::factory()->published()->count(3)->create());
        $laravel->projects()->attach(Project::factory()->create()); // brouillon : non compté
        $react->projects()->attach(Project::factory()->published()->create());

        $this->get('/')
            ->assertSee('3 projets')
            ->assertSee(url('/projets').'?techno=laravel', false)
            ->assertDontSee('1 projet')
            ->assertDontSee('?techno=react', false);

        $this->get('/en')->assertSee('3 projects')->assertSee(url('/en/projects').'?techno=laravel', false);
    }

    public function test_admin_saves_a_level(): void
    {
        $this->actingAs(User::factory()->owner()->create());
        $technology = Technology::create(['name' => 'PHP', 'slug' => 'php', 'category' => 'backend']);

        Livewire::test(TechnologyIndex::class)
            ->call('edit', $technology->id)
            ->set('proficiency', 'expert')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame('expert', $technology->fresh()->proficiency);

        Livewire::test(TechnologyIndex::class)
            ->call('edit', $technology->id)
            ->set('proficiency', '95')
            ->call('save')
            ->assertHasErrors('proficiency');
    }

    public function test_legacy_language_list_is_still_translated(): void
    {
        $profile = ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév'], 'spoken_languages' => [
            ['name' => 'Français', 'level' => 'Natif'],
        ]]);

        $this->assertSame([['name' => 'French', 'level' => 'Native']], $profile->languagesFor('en'));
        $this->assertSame([['name' => 'Français', 'level' => 'Natif']], $profile->languagesFor('fr'));
    }

    public function test_languages_have_an_english_version_in_the_admin(): void
    {
        $this->actingAs(User::factory()->owner()->create());
        ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév'], 'email' => 'test@example.com', 'spoken_languages' => [
            ['name' => 'Français', 'level' => 'Natif'],
            ['name' => 'Fon', 'level' => 'Courant'],
        ]]);

        // L'onglet EN est prérempli avec la traduction automatique.
        $component = Livewire::test(ProfileEditor::class)
            ->assertSet('languages.fr', "Français : Natif\nFon : Courant")
            ->assertSet('languages.en', "French : Native\nFon : Fluent");

        $component->set('languages.en', "French : Native\nFon : Fluent (spoken)")->call('save')->assertHasNoErrors();

        $profile = ProfileSetting::first();
        $this->assertSame('Fluent (spoken)', $profile->languagesFor('en')[1]['level']);
        $this->assertSame('Courant', $profile->languagesFor('fr')[1]['level']);

        $this->get('/en')->assertSee('Fluent (spoken)');
        $this->get('/')->assertSee('Courant');
    }
}
