<?php

namespace Tests\Feature;

use App\Livewire\Admin\Analytics;
use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AnalyticsVisitsTest extends TestCase
{
    use RefreshDatabase;

    private function pageView(string $session, string $path, int $duration, string $title = '', int $minutesAgo = 5): void
    {
        PageView::forceCreate([
            'uuid' => (string) Str::uuid(), 'visitor_hash' => 'v-'.$session, 'session_hash' => $session,
            'path' => $path, 'title' => $title, 'duration_seconds' => $duration,
            'country_code' => 'BJ', 'device' => 'desktop', 'browser' => 'Edge', 'os' => 'Windows',
            'created_at' => now()->subMinutes($minutesAgo),
        ]);
    }

    public function test_pages_of_the_same_visit_are_grouped_on_one_row(): void
    {
        $this->pageView('s1', '/', 44, 'Mahoulolo SEBIO — Développeur', 10);
        $this->pageView('s1', '/projets', 8, 'Projets — Mahoulolo SEBIO', 9);
        $this->pageView('s1', '/en', 48, 'Mahoulolo SEBIO — Developer', 8);
        $this->pageView('s2', '/confidentialite', 0, 'Politique de confidentialité — Mahoulolo SEBIO', 2);

        $this->actingAs(User::factory()->owner()->create());

        Livewire::test(Analytics::class)
            ->assertSee('Dernières visites')
            ->assertSee('3 pages')
            ->assertSeeInOrder(['Accueil', 'Projets', 'Accueil (EN)'])
            ->assertSee('1 min 40 s')   // 44 + 8 + 48 s cumulés
            ->assertSee('1 page');
    }

    public function test_consecutive_reloads_of_a_page_are_merged(): void
    {
        foreach ([10, 9, 8, 7] as $minutes) {
            $this->pageView('s1', '/', 30, 'Mahoulolo SEBIO — Développeur', $minutes);
        }
        $this->pageView('s1', '/blog', 5, 'Blog — Mahoulolo SEBIO', 6);

        $this->actingAs(User::factory()->owner()->create());

        Livewire::test(Analytics::class)
            ->assertSee('5 pages')
            ->assertSee('×4')
            ->assertSeeInOrder(['Accueil', '×4', 'Blog']);
    }

    public function test_page_labels_are_readable(): void
    {
        $this->assertSame('Accueil', Analytics::pageLabel('/'));
        $this->assertSame('Accueil (EN)', Analytics::pageLabel('/en'));
        $this->assertSame('Confidentialité', Analytics::pageLabel('/confidentialite'));
        $this->assertSame('Projets (EN)', Analytics::pageLabel('/en/projects'));
        $this->assertSame('StagesBENIN', Analytics::pageLabel('/projets/stagesbenin', 'StagesBENIN — Mahoulolo SEBIO'));
        $this->assertSame('Africa-Location', Analytics::pageLabel('/projets/africa-location', 'Africa-Location — Mahoulolo SEBIO'));
    }
}
