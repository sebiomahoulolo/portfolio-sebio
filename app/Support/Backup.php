<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

/**
 * Sauvegardes complètes du portfolio : base de données (export SQL généré
 * en PHP, sans mysqldump, pour fonctionner sur un hébergement mutualisé)
 * et fichiers téléversés (storage/app/public), réunis dans une archive zip.
 */
class Backup
{
    /** Tables dont seule la structure est sauvegardée (données temporaires). */
    private const STRUCTURE_ONLY = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

    private const NAME_PATTERN = '/^portfolio-\d{4}-\d{2}-\d{2}-\d{6}\.zip$/';

    public function directory(): string
    {
        return config('portfolio.backups.path');
    }

    /**
     * Crée une sauvegarde et renvoie le chemin de l'archive.
     */
    public function create(): string
    {
        File::ensureDirectoryExists($this->directory(), 0750);

        $name = 'portfolio-'.now()->format('Y-m-d-His').'.zip';
        $target = $this->directory().DIRECTORY_SEPARATOR.$name;
        $temporary = $target.'.part';
        $sql = $this->directory().DIRECTORY_SEPARATOR.'database-'.uniqid().'.sql';

        try {
            $this->dumpDatabase($sql);

            $zip = new ZipArchive;
            if ($zip->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException("Impossible de créer l'archive {$temporary}.");
            }

            $zip->addFile($sql, 'database.sql');
            $zip->addFromString('LISEZ-MOI.txt', $this->readme());
            $this->addUploads($zip);

            if ($password = config('portfolio.backups.password')) {
                $zip->setPassword($password);
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $zip->setEncryptionIndex($i, ZipArchive::EM_AES_256);
                }
            }

            if (! $zip->close()) {
                throw new RuntimeException("L'archive n'a pas pu être finalisée.");
            }

            rename($temporary, $target);
        } finally {
            File::delete([$sql, $temporary]);
        }

        return $target;
    }

    /**
     * Sauvegardes existantes, de la plus récente à la plus ancienne.
     *
     * @return Collection<int, array{name: string, path: string, size: int, created_at: Carbon}>
     */
    public function all(): Collection
    {
        if (! is_dir($this->directory())) {
            return collect();
        }

        return collect(File::files($this->directory()))
            ->filter(fn ($file) => preg_match(self::NAME_PATTERN, $file->getFilename()))
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'created_at' => Carbon::createFromTimestamp($file->getMTime(), config('app.timezone')),
            ])
            ->sortByDesc('name')
            ->values();
    }

    /**
     * Chemin d'une sauvegarde à partir de son nom, ou null si le nom est invalide
     * (aucun accès possible en dehors du dossier des sauvegardes).
     */
    public function find(string $name): ?string
    {
        $path = $this->directory().DIRECTORY_SEPARATOR.$name;

        return preg_match(self::NAME_PATTERN, $name) && is_file($path) ? $path : null;
    }

    public function delete(string $name): bool
    {
        $path = $this->find($name);

        return $path !== null && File::delete($path);
    }

    /**
     * Supprime les sauvegardes trop anciennes, en gardant toujours les plus récentes.
     *
     * @return int nombre d'archives supprimées
     */
    public function prune(): int
    {
        $limit = now()->subDays((int) config('portfolio.backups.keep_days'));
        $minimum = (int) config('portfolio.backups.keep_minimum');

        return $this->all()
            ->slice($minimum)
            ->filter(fn ($backup) => $backup['created_at']->lt($limit))
            ->each(fn ($backup) => File::delete($backup['path']))
            ->count();
    }

    private function dumpDatabase(string $path): void
    {
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        $handle = fopen($path, 'wb');

        try {
            fwrite($handle, '-- Sauvegarde du portfolio · '.now()->toDateTimeString()."\n");
            fwrite($handle, '-- Base : '.$connection->getDatabaseName()."\n\n");
            fwrite($handle, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

            $tables = collect($connection->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"))
                ->map(fn ($row) => array_values((array) $row)[0]);

            foreach ($tables as $table) {
                $create = array_values((array) $connection->selectOne("SHOW CREATE TABLE `{$table}`"))[1];
                fwrite($handle, "-- Table {$table}\nDROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n");

                if (in_array($table, self::STRUCTURE_ONLY, true)) {
                    continue;
                }

                $statement = $pdo->query("SELECT * FROM `{$table}`");
                $columns = null;
                $rows = [];

                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $columns ??= array_keys($row);
                    $rows[] = '('.implode(', ', array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), $row)).')';

                    if (count($rows) === 200) {
                        $this->writeInsert($handle, $table, $columns, $rows);
                        $rows = [];
                    }
                }

                if ($rows) {
                    $this->writeInsert($handle, $table, $columns, $rows);
                }
                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        } finally {
            fclose($handle);
        }
    }

    private function writeInsert($handle, string $table, array $columns, array $rows): void
    {
        $columns = implode(', ', array_map(fn ($column) => "`{$column}`", $columns));
        fwrite($handle, "INSERT INTO `{$table}` ({$columns}) VALUES\n".implode(",\n", $rows).";\n");
    }

    private function addUploads(ZipArchive $zip): void
    {
        $root = storage_path('app/public');
        if (! is_dir($root)) {
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getFilename() !== '.gitignore') {
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
                $zip->addFile($file->getPathname(), 'storage/app/public/'.$relative);
            }
        }
    }

    private function readme(): string
    {
        return <<<TXT
        Sauvegarde du portfolio — {$this->now()}

        Contenu
          database.sql            export complet de la base de données
          storage/app/public/     images et fichiers téléversés depuis l'administration

        Restauration
          1. Importer database.sql (phpMyAdmin → Importer, ou : mysql -u UTILISATEUR -p BASE < database.sql)
          2. Copier storage/app/public/ dans le dossier storage/app/public/ du site
          3. Conserver la même APP_KEY dans le fichier .env : elle déchiffre les secrets
             de double authentification et les sessions. Sans elle, il faudra réactiver la 2FA.
        TXT;
    }

    private function now(): string
    {
        return now()->format('d/m/Y H:i');
    }
}
