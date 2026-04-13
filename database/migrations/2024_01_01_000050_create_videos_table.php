<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pôle Contenu - Vidéos courtes (type TikTok)
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url');
            $table->string('thumbnail_url')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->enum('status', ['processing', 'published', 'rejected', 'deleted'])->default('processing');
            $table->enum('content_type', ['citoyen', 'solution', 'terrain', 'education', 'autre'])->default('citoyen');
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->boolean('comments_enabled')->default(true);
            $table->double('algorithm_score')->default(0)->comment('Score algorithme de recommandation');
            $table->json('hashtags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'algorithm_score']);
            $table->index(['user_id', 'status']);
        });

        // Likes sur les vidéos
        Schema::create('video_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'video_id']);
        });

        // Partages
        Schema::create('video_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['internal', 'whatsapp', 'facebook', 'other'])->default('internal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_shares');
        Schema::dropIfExists('video_likes');
        Schema::dropIfExists('videos');
    }
};
