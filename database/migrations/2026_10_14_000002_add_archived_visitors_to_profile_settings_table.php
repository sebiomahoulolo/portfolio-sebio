<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            // Visiteurs des mesures d'audience supprimées après 25 mois :
            // le compteur public reste juste malgré la purge.
            $table->unsignedBigInteger('archived_visitors')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            $table->dropColumn('archived_visitors');
        });
    }
};
