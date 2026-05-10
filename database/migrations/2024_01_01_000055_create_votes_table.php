<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pôle Démocratie Participative - Votes
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('total_votes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'status']);
        });

        // Options d'un vote
        Schema::create('vote_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('description')->nullable();
            $table->integer('votes_count')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Votes des utilisateurs
        Schema::create('user_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vote_option_id')->constrained('vote_options')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'vote_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_votes');
        Schema::dropIfExists('vote_options');
        Schema::dropIfExists('votes');
    }
};
