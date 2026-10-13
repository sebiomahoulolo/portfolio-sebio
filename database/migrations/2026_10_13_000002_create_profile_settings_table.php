<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profil public du propriétaire : une seule ligne. Les colonnes JSON
     * marquées « traduit » contiennent une valeur par langue ({"fr": "...", "en": "..."}).
     */
    public function up(): void
    {
        Schema::create('profile_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('headline');            // traduit — ex. « Développeur Full Stack »
            $table->json('subtitle')->nullable(); // traduit
            $table->json('tagline')->nullable();  // traduit — accroche courte, ex. « Je bâtis des SaaS sécurisés. »
            $table->json('expertise')->nullable(); // traduit — { fr: [...], en: [...] } badges d'expertise
            $table->unsignedTinyInteger('years_experience')->nullable(); // vide : calculé depuis le parcours
            $table->json('intro')->nullable();    // traduit — texte du hero
            $table->json('about')->nullable();    // traduit — « Qui suis-je ? »
            $table->json('objective')->nullable(); // traduit
            $table->json('current_position')->nullable(); // traduit
            $table->json('featured_project')->nullable();  // traduit — projet phare
            $table->string('featured_project_url')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('location')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->json('social_links')->nullable(); // { github, linkedin, facebook, ... }
            $table->json('spoken_languages')->nullable(); // [{ name, level }]
            $table->json('interests')->nullable();        // traduit — { fr: [...], en: [...] }
            $table->boolean('is_available')->default(true);
            $table->json('seo_title')->nullable();       // traduit
            $table->json('seo_description')->nullable(); // traduit
            $table->string('og_image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_settings');
    }
};
