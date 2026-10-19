<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Adresses traduites pour la version anglaise des articles, services et projets.
 * Les contenus existants reçoivent un slug tiré de leur titre anglais.
 */
return new class extends Migration
{
    private const TABLES = ['posts', 'services', 'projects'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('slug_en', 160)->nullable()->unique()->after('slug');
            });

            $taken = [];
            foreach (DB::table($table)->orderBy('id')->get(['id', 'slug', 'title']) as $row) {
                $slug = Str::slug((string) (json_decode((string) $row->title, true)['en'] ?? ''));
                if ($slug === '' || $slug === $row->slug) {
                    continue;
                }

                $candidate = $slug;
                for ($i = 2; in_array($candidate, $taken, true); $i++) {
                    $candidate = $slug.'-'.$i;
                }
                $taken[] = $candidate;

                DB::table($table)->where('id', $row->id)->update(['slug_en' => $candidate]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropUnique(['slug_en']);
                $blueprint->dropColumn('slug_en');
            });
        }
    }
};
