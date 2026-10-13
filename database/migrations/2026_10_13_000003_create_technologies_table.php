<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 100)->unique();
            $table->string('category', 20)->default('other')->index(); // frontend, backend, tools, other
            $table->unsignedTinyInteger('level')->nullable(); // 0-100, affiché dans « Compétences »
            $table->string('icon', 60)->nullable();           // classe Font Awesome, ex. « fab fa-laravel »
            $table->boolean('show_in_skills')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technologies');
    }
};
