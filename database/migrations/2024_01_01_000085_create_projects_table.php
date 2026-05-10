<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Espace de présentation de projets
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('cover_url')->nullable();
            $table->json('images')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('supports_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->enum('category', ['social', 'economique', 'educatif', 'environnement', 'sante', 'technologie', 'autre'])->default('autre');
            $table->enum('status', ['draft', 'published', 'completed', 'abandoned'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'status']);
        });

        // Soutiens aux projets
        Schema::create('project_supports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'project_id']);
        });

        // Espace de coopération Afrique-Haïti
        Schema::create('cooperation_projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('cover_url')->nullable();
            $table->string('countries')->nullable()->comment('Ex: Sénégal, Côte d\'Ivoire');
            $table->enum('sector', ['business', 'education', 'agriculture', 'sante', 'technologie', 'culture', 'autre'])->default('autre');
            $table->string('contact_email')->nullable();
            $table->string('organization')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('interests_count')->default(0);
            $table->timestamps();
        });

        // Intérêts exprimés pour cooperation
        Schema::create('cooperation_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cooperation_project_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'cooperation_project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperation_interests');
        Schema::dropIfExists('cooperation_projects');
        Schema::dropIfExists('project_supports');
        Schema::dropIfExists('projects');
    }
};
