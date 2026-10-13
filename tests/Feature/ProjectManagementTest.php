<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Livewire\Admin\Projects\ProjectForm;
use App\Livewire\Admin\Projects\ProjectIndex;
use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->owner()->create();
    }

    public function test_owner_can_create_a_draft_with_minimal_information(): void
    {
        Livewire::actingAs($this->owner)
            ->test(ProjectForm::class)
            ->set('form.title.fr', 'Plateforme RH')
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::firstWhere('slug', 'plateforme-rh');
        $this->assertNotNull($project);
        $this->assertSame(PublicationStatus::Draft, $project->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'project.created']);
    }

    public function test_publication_requires_an_excerpt(): void
    {
        Livewire::actingAs($this->owner)
            ->test(ProjectForm::class)
            ->set('form.title.fr', 'Projet incomplet')
            ->set('form.status', 'published')
            ->call('save')
            ->assertHasErrors('form.status');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_owner_can_publish_a_complete_project_with_technologies_and_images(): void
    {
        Storage::fake('public');
        $laravel = Technology::create(['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'backend']);

        Livewire::actingAs($this->owner)
            ->test(ProjectForm::class)
            ->set('form.title.fr', 'Gestion de carburant')
            ->set('form.excerpt.fr', 'Suivi de la consommation de carburant des véhicules de terrain.')
            ->set('form.demo_url', 'https://exemple.com')
            ->set('form.status', 'published')
            ->set('technologyIds', [$laravel->id])
            ->set('cover', UploadedFile::fake()->image('cover.jpg', 1600, 1000))
            ->set('gallery', [UploadedFile::fake()->image('a.png', 800, 600), UploadedFile::fake()->image('b.webp', 800, 600)])
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::with('technologies', 'images')->firstWhere('slug', 'gestion-de-carburant');
        $this->assertTrue($project->isPubliclyVisible());
        $this->assertTrue($project->technologies->contains($laravel));
        $this->assertCount(2, $project->images);
        Storage::disk('public')->assertExists($project->cover_image);

        $this->get(route('projects.show', $project))->assertOk()->assertSee('Gestion de carburant');
    }

    public function test_external_links_must_use_https(): void
    {
        Livewire::actingAs($this->owner)
            ->test(ProjectForm::class)
            ->set('form.title.fr', 'Projet')
            ->set('form.demo_url', 'http://non-securise.com')
            ->set('form.repository_url', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['form.demo_url', 'form.repository_url']);
    }

    public function test_uploaded_cover_must_be_a_real_image(): void
    {
        Livewire::actingAs($this->owner)
            ->test(ProjectForm::class)
            ->set('form.title.fr', 'Projet')
            ->set('cover', UploadedFile::fake()->create('cover.jpg', 10, 'application/x-php'))
            ->call('save')
            ->assertHasErrors('cover');
    }

    public function test_slug_must_be_unique(): void
    {
        Project::factory()->create(['slug' => 'doublon']);

        Livewire::actingAs($this->owner)
            ->test(ProjectForm::class)
            ->set('form.title.fr', 'Autre')
            ->set('form.slug', 'doublon')
            ->call('save')
            ->assertHasErrors('form.slug');
    }

    public function test_search_and_filters_are_applied_before_pagination(): void
    {
        Project::factory()->count(20)->published()->create();
        $target = Project::factory()->create(['title' => ['fr' => 'Introuvable Ailleurs'], 'slug' => 'introuvable']);

        Livewire::actingAs($this->owner)
            ->test(ProjectIndex::class)
            ->set('search', 'Introuvable')
            ->assertSee('Introuvable Ailleurs')
            ->assertViewHas('statuses');

        $component = Livewire::actingAs($this->owner)->test(ProjectIndex::class)->set('status', 'draft');
        $this->assertSame([$target->id], $component->instance()->projects->pluck('id')->all());
    }

    public function test_deleted_project_goes_to_trash_and_can_be_restored(): void
    {
        $project = Project::factory()->published()->create();

        Livewire::actingAs($this->owner)
            ->test(ProjectIndex::class)
            ->call('confirmDelete', $project->id)
            ->call('delete');

        $this->assertSoftDeleted($project);
        $this->get(route('projects.show', $project->slug))->assertNotFound();

        Livewire::actingAs($this->owner)->test(ProjectIndex::class)->call('restore', $project->id);
        $this->assertNotSoftDeleted($project);
    }
}
