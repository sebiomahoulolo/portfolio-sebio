<?php

namespace App\Livewire\Admin;

use App\Support\ActivityLogger;
use App\Support\Backup;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

#[Layout('components.layouts.admin')]
#[Title('Sauvegardes')]
class Backups extends Component
{
    public function mount(): void
    {
        Gate::authorize('manage-settings');
    }

    public function create(Backup $backup): void
    {
        Gate::authorize('manage-settings');

        // Une sauvegarde manuelle par minute au plus : l'opération est lourde.
        if (! RateLimiter::attempt('backup:manual', 1, fn () => true, 60)) {
            Flux::toast('Une sauvegarde vient d’être lancée. Patientez une minute avant d’en créer une autre.', variant: 'warning');

            return;
        }

        try {
            $path = $backup->create();
            $backup->prune();
        } catch (Throwable $e) {
            Log::error('Échec de la sauvegarde manuelle', ['error' => $e->getMessage()]);
            Flux::toast('La sauvegarde a échoué. Le détail est enregistré dans le journal du serveur.', variant: 'danger');

            return;
        }

        ActivityLogger::log('backup.created', 'Sauvegarde manuelle créée : '.basename($path));
        Flux::toast('Sauvegarde créée.', variant: 'success');
    }

    public function download(Backup $backup, string $name): ?BinaryFileResponse
    {
        Gate::authorize('manage-settings');

        $path = $backup->find($name);
        if (! $path) {
            Flux::toast('Cette sauvegarde n’existe plus.', variant: 'danger');

            return null;
        }

        ActivityLogger::log('backup.downloaded', 'Sauvegarde téléchargée : '.$name);

        return response()->download($path);
    }

    public function delete(Backup $backup, string $name): void
    {
        Gate::authorize('manage-settings');

        if ($backup->delete($name)) {
            ActivityLogger::log('backup.deleted', 'Sauvegarde supprimée : '.$name);
            Flux::toast('Sauvegarde supprimée.', variant: 'success');
        }
    }

    public function render(Backup $backup)
    {
        $backups = $backup->all();

        return view('livewire.admin.backups', [
            'backups' => $backups,
            'latest' => $backups->first(),
            'encrypted' => filled(config('portfolio.backups.password')),
            'keepDays' => config('portfolio.backups.keep_days'),
        ]);
    }
}
