<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('contact'); // contact, quote
            $table->string('name', 120);
            $table->string('email', 190)->index();
            $table->string('phone', 40)->nullable();
            $table->string('company', 160)->nullable();
            $table->string('subject', 190);
            $table->text('message');
            $table->string('budget', 60)->nullable();
            $table->string('status', 20)->default('new')->index(); // new, read, archived
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
