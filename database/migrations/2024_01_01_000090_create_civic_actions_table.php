<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pôle Mobilisation Citoyenne
        Schema::create('civic_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('thumbnail')->nullable();
            $table->enum('type', ['nettoyage', 'reunion', 'action_legale', 'sensibilisation', 'petition', 'autre'])->default('autre');
            $table->enum('status', ['planned', 'active', 'completed', 'cancelled'])->default('planned');
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('action_date');
            $table->integer('participants_needed')->nullable();
            $table->integer('participants_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Participants aux actions citoyennes
        Schema::create('civic_action_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('civic_action_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['participant', 'organizer', 'volunteer'])->default('participant');
            $table->enum('status', ['registered', 'confirmed', 'attended', 'cancelled'])->default('registered');
            $table->timestamps();
            $table->unique(['user_id', 'civic_action_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civic_action_participants');
        Schema::dropIfExists('civic_actions');
    }
};
