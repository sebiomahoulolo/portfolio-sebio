<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->json('title');                   // traduit
            $table->string('slug', 160)->unique();
            $table->json('summary')->nullable();     // traduit — phrase d'accroche de la carte
            $table->json('description')->nullable(); // traduit — texte de la page dédiée
            $table->json('features')->nullable();    // traduit — { fr: [...], en: [...] } ce qui est inclus
            $table->json('price_label')->nullable(); // traduit — ex. « Sur devis »
            $table->string('icon', 60)->nullable();  // classe Font Awesome
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('seo_description')->nullable(); // traduit
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name', 120);
            $table->json('author_title')->nullable(); // traduit — fonction et entreprise
            $table->json('quote');                   // traduit
            $table->string('source', 40)->default('linkedin'); // linkedin, client, other
            $table->string('source_url')->nullable();
            $table->string('avatar_path')->nullable();
            $table->date('given_at')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('services');
    }
};
