<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les pourcentages de compétence (« PHP 85 % ») deviennent des niveaux
 * lisibles : Expert, Confirmé, Notions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technologies', function (Blueprint $table) {
            $table->string('proficiency', 12)->nullable()->after('category');
        });

        DB::table('technologies')->whereNotNull('level')->update([
            'proficiency' => DB::raw("CASE WHEN level >= 90 THEN 'expert' WHEN level >= 75 THEN 'confirmed' ELSE 'notions' END"),
        ]);

        Schema::table('technologies', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }

    public function down(): void
    {
        Schema::table('technologies', function (Blueprint $table) {
            $table->unsignedTinyInteger('level')->nullable()->after('category');
        });

        DB::table('technologies')->whereNotNull('proficiency')->update([
            'level' => DB::raw("CASE proficiency WHEN 'expert' THEN 90 WHEN 'confirmed' THEN 80 ELSE 60 END"),
        ]);

        Schema::table('technologies', function (Blueprint $table) {
            $table->dropColumn('proficiency');
        });
    }
};
