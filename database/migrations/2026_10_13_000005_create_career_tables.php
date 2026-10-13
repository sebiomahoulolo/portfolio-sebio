<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->json('title');                // traduit — intitulé du poste
            $table->string('company', 160);
            $table->string('location', 120)->nullable();
            $table->string('employment_type', 30)->nullable(); // cdi, freelance, stage...
            $table->json('description')->nullable(); // traduit
            $table->date('started_at');
            $table->date('ended_at')->nullable();  // null = poste en cours
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->json('degree');               // traduit
            $table->json('field')->nullable();    // traduit
            $table->string('institution', 160);
            $table->string('location', 120)->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->json('name');                 // traduit
            $table->string('issuer', 160);
            $table->date('issued_at')->nullable();
            $table->string('credential_url')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('experiences');
    }
};
