<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pôle Live - Diffusions en direct
        Schema::create('lives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('stream_key')->unique()->nullable();
            $table->string('stream_url')->nullable();
            $table->string('playback_url')->nullable();
            $table->enum('type', ['discussion', 'debat', 'campagne', 'information', 'autre'])->default('discussion');
            $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled'])->default('scheduled');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('viewers_count')->default(0);
            $table->integer('peak_viewers')->default(0);
            $table->integer('likes_count')->default(0);
            $table->boolean('is_recorded')->default(true);
            $table->string('recording_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Participants actifs aux lives
        Schema::create('live_viewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('live_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_viewers');
        Schema::dropIfExists('lives');
    }
};
