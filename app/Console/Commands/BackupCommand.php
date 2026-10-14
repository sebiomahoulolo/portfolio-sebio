<?php

namespace App\Console\Commands;

use App\Support\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Number;
use Throwable;

class BackupCommand extends Command
{
    protected $signature = 'portfolio:backup';

    protected $description = 'Sauvegarde la base de données et les fichiers téléversés, puis supprime les anciennes sauvegardes';

    public function handle(Backup $backup): int
    {
        try {
            $path = $backup->create();
            $pruned = $backup->prune();
        } catch (Throwable $e) {
            Log::error('Échec de la sauvegarde automatique', ['error' => $e->getMessage()]);
            $this->alertOwner($e);
            $this->error('Échec de la sauvegarde : '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Sauvegarde créée : '.basename($path).' ('.Number::fileSize(filesize($path), 1).')');
        if ($pruned) {
            $this->line("{$pruned} ancienne(s) sauvegarde(s) supprimée(s).");
        }

        return self::SUCCESS;
    }

    private function alertOwner(Throwable $e): void
    {
        try {
            Mail::raw(
                'La sauvegarde automatique du portfolio a échoué le '.now()->format('d/m/Y à H:i').".\n\nErreur : {$e->getMessage()}\n\nVérifiez l'espace disque et les journaux (storage/logs), puis lancez une sauvegarde manuelle depuis l'administration (Sauvegardes).",
                fn ($message) => $message->to(config('portfolio.notify_email'))->subject('⚠ Échec de la sauvegarde du portfolio'),
            );
        } catch (Throwable) {
            // L'erreur est déjà journalisée.
        }
    }
}
