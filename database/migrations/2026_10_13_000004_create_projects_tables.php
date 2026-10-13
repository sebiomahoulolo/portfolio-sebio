<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->json('title');                 // traduit
            $table->string('slug', 160)->unique();
            $table->json('excerpt')->nullable();   // traduit — résumé de la carte
            $table->json('description')->nullable(); // traduit — étude de cas
            $table->json('role')->nullable();      // traduit — mon rôle sur le projet
            $table->string('category', 80)->nullable()->index();
            $table->string('client', 120)->nullable();
            $table->string('period', 60)->nullable();
            $table->string('status', 20)->default('draft')->index(); // draft, published, archived
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('cover_image')->nullable();
            $table->json('cover_alt')->nullable(); // traduit
            $table->string('demo_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->json('seo_title')->nullable();       // traduit
            $table->json('seo_description')->nullable(); // traduit
            $table->boolean('is_indexable')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->json('alt')->nullable(); // traduit
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_technology', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['project_id', 'technology_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_technology');
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('projects');
    }
};
