<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Technology;
use Database\Seeders\PortfolioContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_seeded_content(): void
    {
        $this->seed(PortfolioContentSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('Mahoulolo')
            ->assertSee('StagesBENIN')
            ->assertSee('contact@mahoulolo-sebio.com')
            ->assertSee('application/ld+json', false);
    }

    public function test_home_page_works_with_an_empty_database(): void
    {
        $this->get('/')->assertOk();
        $this->get('/projets')->assertOk();
    }

    public function test_drafts_scheduled_and_trashed_projects_are_hidden(): void
    {
        $published = Project::factory()->published()->create(['title' => ['fr' => 'Projet Public']]);
        $draft = Project::factory()->create(['title' => ['fr' => 'Projet Brouillon']]);
        $scheduled = Project::factory()->scheduled()->create(['title' => ['fr' => 'Projet Programme']]);
        $trashed = Project::factory()->published()->create(['title' => ['fr' => 'Projet Supprime']]);
        $trashed->delete();

        $this->get('/projets')
            ->assertSee('Projet Public')
            ->assertDontSee('Projet Brouillon')
            ->assertDontSee('Projet Programme')
            ->assertDontSee('Projet Supprime');

        $this->get(route('projects.show', $published->slug))->assertOk();
        $this->get(route('projects.show', $draft->slug))->assertNotFound();
        $this->get(route('projects.show', $scheduled->slug))->assertNotFound();
        $this->get(route('projects.show', $trashed->slug))->assertNotFound();
    }

    public function test_project_views_are_counted_once_per_session(): void
    {
        $project = Project::factory()->published()->create();

        $this->get(route('projects.show', $project->slug));
        $this->get(route('projects.show', $project->slug));

        $this->assertSame(1, $project->fresh()->views_count);
    }

    public function test_projects_can_be_filtered_by_technology(): void
    {
        $vue = Technology::create(['name' => 'Vue', 'slug' => 'vue', 'category' => 'frontend']);
        Project::factory()->published()->create(['title' => ['fr' => 'Avec Vue']])->technologies()->attach($vue);
        Project::factory()->published()->create(['title' => ['fr' => 'Sans Vue']]);

        $this->get('/projets?techno=vue')->assertSee('Avec Vue')->assertDontSee('Sans Vue');
    }

    public function test_case_study_text_is_escaped(): void
    {
        $project = Project::factory()->published()->create([
            'description' => ['fr' => "## Contexte\n<script>alert('x')</script>"],
        ]);

        $this->get(route('projects.show', $project->slug))
            ->assertOk()
            ->assertSee('<h2>Contexte</h2>', false)
            ->assertDontSee("<script>alert('x')</script>", false);
    }

    public function test_sitemap_lists_only_public_indexable_projects(): void
    {
        $visible = Project::factory()->published()->create();
        $hidden = Project::factory()->published()->create(['is_indexable' => false]);
        $draft = Project::factory()->create();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('projects.show', $visible->slug))
            ->assertDontSee(route('projects.show', $hidden->slug))
            ->assertDontSee(route('projects.show', $draft->slug));
    }

    public function test_cv_download_falls_back_to_the_legacy_file(): void
    {
        $this->get(route('cv'))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }
}
