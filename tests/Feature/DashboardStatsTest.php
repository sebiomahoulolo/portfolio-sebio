<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Models\PageView;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_visitors_and_project_views_detail(): void
    {
        Project::create(['slug' => 'stages', 'title' => ['fr' => 'StagesBENIN'], 'views_count' => 12]);
        Project::create(['slug' => 'africode', 'title' => ['fr' => 'AfriCode'], 'views_count' => 3]);
        PageView::create(['uuid' => (string) str()->uuid(), 'visitor_hash' => 'a', 'session_hash' => 's1', 'path' => '/']);

        Livewire::actingAs(User::factory()->owner()->create())
            ->test(Dashboard::class)
            ->assertSee('Visiteurs')
            ->assertSee('1 aujourd’hui')
            ->assertSee('Consultations par projet')
            ->assertSeeInOrder(['StagesBENIN', '12', 'AfriCode', '3']);
    }
}
