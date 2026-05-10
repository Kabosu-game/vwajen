<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Espace Musée des Révolutionnaires — Mémoire historique numérique
        Schema::create('museum_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('category', [
                'leader_revolutionnaire_haitien',
                'mouvement_historique',
                'pensee_politique_ideologique',
                'personnage_africain',
                'international'
            ])->default('leader_revolutionnaire_haitien');
            $table->text('biography')->nullable();
            $table->text('political_thought')->nullable()->comment('Pensée politique');
            $table->string('period')->nullable()->comment('Ex: 1750-1803');
            $table->string('birth_year')->nullable();
            $table->string('death_year')->nullable();
            $table->string('nationality')->nullable();
            $table->string('portrait_url')->nullable();
            $table->json('gallery')->nullable()->comment('Photos, illustrations');
            $table->json('speeches')->nullable()->comment('Discours: [{title, content, year}]');
            $table->json('documents')->nullable()->comment('Documents historiques: [{title, url, year}]');
            $table->json('videos')->nullable()->comment('Vidéos éducatives: [{title, url, duration}]');
            $table->json('timeline')->nullable()->comment('Timeline: [{year, event, description}]');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->integer('views_count')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_published']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('museum_entries');
    }
};
