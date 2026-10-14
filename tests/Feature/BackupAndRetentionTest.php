<?php

namespace Tests\Feature;

use App\Livewire\Admin\Backups;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\PageView;
use App\Models\ProfileSetting;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\Backup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class BackupAndRetentionTest extends TestCase
{
    use RefreshDatabase;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('framework/testing/backups-'.Str::random(6));
        config(['portfolio.backups.path' => $this->directory]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_backup_command_creates_an_archive_with_the_database(): void
    {
        ContactMessage::factory()->create(['name' => 'Client Sauvegardé']);

        $this->artisan('portfolio:backup')->assertSuccessful();

        $archives = (new Backup)->all();
        $this->assertCount(1, $archives);

        $zip = new ZipArchive;
        $zip->open($archives->first()['path']);
        $sql = $zip->getFromName('database.sql');
        $this->assertNotFalse($zip->locateName('LISEZ-MOI.txt'));
        $zip->close();

        $this->assertStringContainsString('CREATE TABLE `contact_messages`', $sql);
        $this->assertStringContainsString('Client Sauvegardé', $sql);
        $this->assertStringNotContainsString('INSERT INTO `sessions`', $sql);
    }

    public function test_archive_is_encrypted_when_a_password_is_set(): void
    {
        config(['portfolio.backups.password' => 'secret-de-test']);

        $path = (new Backup)->create();

        $zip = new ZipArchive;
        $zip->open($path);
        $this->assertSame(ZipArchive::EM_AES_256, $zip->statName('database.sql')['encryption_method']);
        $this->assertFalse($zip->getFromName('database.sql'));
        $zip->setPassword('secret-de-test');
        $this->assertStringContainsString('CREATE TABLE', $zip->getFromName('database.sql'));
        $zip->close();
    }

    public function test_old_backups_are_pruned_but_the_latest_are_kept(): void
    {
        File::ensureDirectoryExists($this->directory);
        foreach (range(1, 5) as $day) {
            $path = $this->directory.'/portfolio-2026-01-0'.$day.'-030000.zip';
            File::put($path, 'x');
            touch($path, now()->subDays(30 - $day)->getTimestamp());
        }

        $this->assertSame(2, (new Backup)->prune());
        $this->assertCount(3, (new Backup)->all());
    }

    public function test_backup_names_cannot_escape_the_backup_folder(): void
    {
        $this->assertNull((new Backup)->find('../../.env'));
        $this->assertFalse((new Backup)->delete('../database.sql'));
    }

    public function test_only_the_owner_can_manage_backups(): void
    {
        $this->actingAs(User::factory()->editor()->create())->get('/admin/sauvegardes')->assertForbidden();

        $this->actingAs(User::factory()->owner()->create())->get('/admin/sauvegardes')->assertOk()->assertSee('Aucune sauvegarde');

        Livewire::test(Backups::class)->call('create');
        $this->assertCount(1, (new Backup)->all());
        $this->assertDatabaseHas('activity_logs', ['action' => 'backup.created']);
    }

    public function test_retention_periods_are_applied(): void
    {
        $profile = ProfileSetting::create(['name' => 'Test', 'headline' => ['fr' => 'Dév']]);

        $oldMessage = ContactMessage::factory()->create(['created_at' => now()->subMonths(37)]);
        $recentMessage = ContactMessage::factory()->create();

        $oldPending = Testimonial::forceCreate(['author_name' => 'A', 'quote' => ['fr' => 'Bien'], 'status' => 'pending', 'created_at' => now()->subMonths(7)]);
        $oldPublished = Testimonial::forceCreate(['author_name' => 'B', 'quote' => ['fr' => 'Top'], 'status' => 'approved', 'created_at' => now()->subYears(3)]);

        ActivityLog::forceCreate(['action' => 'test', 'description' => 'Ancien', 'created_at' => now()->subMonths(13)]);

        foreach ([['old-1', 26], ['old-1', 26], ['old-2', 27], ['new', 0]] as [$hash, $months]) {
            PageView::forceCreate(['uuid' => (string) Str::uuid(), 'visitor_hash' => $hash, 'session_hash' => 's', 'path' => '/', 'created_at' => now()->subMonths($months)]);
        }
        $countBefore = PageView::publicVisitorCount();

        $this->artisan('portfolio:prune-data')->assertSuccessful();

        $this->assertModelMissing($oldMessage);
        $this->assertModelExists($recentMessage);
        $this->assertModelMissing($oldPending);
        $this->assertModelExists($oldPublished);
        $this->assertSame(0, ActivityLog::where('action', 'test')->count());
        $this->assertSame(1, PageView::count());
        $this->assertSame(2, $profile->fresh()->archived_visitors);
        // Le compteur public du site ne baisse pas après la purge.
        $this->assertSame($countBefore, PageView::publicVisitorCount());
    }

    public function test_tasks_are_scheduled(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('portfolio:backup')
            ->expectsOutputToContain('portfolio:prune-data')
            ->assertSuccessful();
    }
}
