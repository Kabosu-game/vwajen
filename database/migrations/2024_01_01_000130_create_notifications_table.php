<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Bibliothèque de contenu - Enregistrements sauvegardés
        Schema::create('saved_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('saveable'); // video, live (recording), course
            $table->timestamps();
            $table->unique(['user_id', 'saveable_type', 'saveable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_contents');
        Schema::dropIfExists('notifications');
    }
};
