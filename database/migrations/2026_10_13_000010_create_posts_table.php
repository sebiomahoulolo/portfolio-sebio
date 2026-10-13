<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('title');               // traduit
            $table->string('slug', 160)->unique();
            $table->json('excerpt')->nullable(); // traduit
            $table->json('body')->nullable();    // traduit — texte avec intertitres (##), listes (-) et blocs de code (```)
            $table->json('tags')->nullable();    // ["Laravel", "Sécurité"]
            $table->string('cover_image')->nullable();
            $table->string('status', 20)->default('draft')->index(); // draft, published, archived
            $table->json('seo_title')->nullable();       // traduit
            $table->json('seo_description')->nullable(); // traduit
            $table->boolean('is_indexable')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
