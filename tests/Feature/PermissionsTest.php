<?php

namespace Tests\Feature;

use App\Livewire\Admin\Projects\ProjectIndex;
use App\Livewire\Admin\Technologies\TechnologyIndex;
use App\Models\ContactMessage;
use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_manage_projects_but_not_private_areas(): void
    {
        $editor = User::factory()->editor()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($editor);

        $this->get('/admin')->assertOk();
        $this->get('/admin/projets')->assertOk();
        $this->get('/admin/projets/nouveau')->assertOk();
        $this->get('/admin/technologies')->assertOk();

        // Données personnelles des contacts, profil public et journal : propriétaire uniquement.
        $this->get('/admin/messages')->assertForbidden();
        $this->get('/admin/messages/'.$message->id)->assertForbidden();
        $this->get('/admin/profil')->assertForbidden();
        $this->get('/admin/journal')->assertForbidden();

        // Et le message n'est pas marqué comme lu par une tentative refusée.
        $this->assertSame('new', $message->fresh()->status->value);
    }

    public function test_owner_can_access_everything(): void
    {
        $this->actingAs(User::factory()->owner()->create());

        foreach (['/admin', '/admin/projets', '/admin/technologies', '/admin/messages', '/admin/profil', '/admin/journal', '/admin/compte'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_editor_cannot_delete_a_project(): void
    {
        $project = Project::factory()->published()->create();

        Livewire::actingAs(User::factory()->editor()->create())
            ->test(ProjectIndex::class)
            ->call('confirmDelete', $project->id)
            ->call('delete')
            ->assertForbidden();

        $this->assertNotSoftDeleted($project);
    }

    public function test_bulk_delete_checks_permission_on_each_project(): void
    {
        $projects = Project::factory()->count(2)->published()->create();

        Livewire::actingAs(User::factory()->editor()->create())
            ->test(ProjectIndex::class)
            ->set('selected', $projects->pluck('id')->all())
            ->call('bulk', 'delete');

        foreach ($projects as $project) {
            $this->assertNotSoftDeleted($project);
        }
    }

    public function test_owner_bulk_actions_apply_to_selected_projects_only(): void
    {
        [$a, $b, $c] = Project::factory()->count(3)->create();

        Livewire::actingAs(User::factory()->owner()->create())
            ->test(ProjectIndex::class)
            ->set('selected', [$a->id, $b->id])
            ->call('bulk', 'publish');

        $this->assertTrue($a->fresh()->isPubliclyVisible());
        $this->assertTrue($b->fresh()->isPubliclyVisible());
        $this->assertFalse($c->fresh()->isPubliclyVisible());
    }

    public function test_editor_cannot_delete_a_technology(): void
    {
        $technology = Technology::create(['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'backend']);

        Livewire::actingAs(User::factory()->editor()->create())
            ->test(TechnologyIndex::class)
            ->call('delete', $technology->id)
            ->assertForbidden();

        $this->assertModelExists($technology);
    }
}
