<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Statistiques de fréquentation, sans donnée personnelle : l'adresse IP
     * n'est jamais stockée, le visiteur est identifié par une empreinte
     * hachée qui change chaque jour (comptage des visiteurs uniques par jour).
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('visitor_hash', 64)->index();  // empreinte quotidienne anonyme
            $table->string('session_hash', 64)->index();  // regroupe les pages d'une même visite
            $table->string('path', 255)->index();
            $table->string('title', 190)->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('referrer_host', 120)->nullable()->index();
            $table->char('country_code', 2)->nullable()->index();
            $table->string('device', 10)->nullable();  // desktop, mobile, tablet
            $table->string('browser', 30)->nullable();
            $table->string('os', 30)->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('created_at')->useCurrent()->index();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
