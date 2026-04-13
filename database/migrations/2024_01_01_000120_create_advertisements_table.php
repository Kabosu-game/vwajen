<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pôle Publicité & Communication
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('link_url')->nullable();
            $table->enum('type', ['civic_campaign', 'action_promotion', 'project', 'announcement'])->default('announcement');
            $table->enum('placement', ['feed', 'story', 'banner', 'interstitiel'])->default('feed');
            $table->enum('status', ['draft', 'pending_approval', 'active', 'paused', 'ended', 'rejected'])->default('draft');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('impressions_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->integer('budget')->default(0)->comment('Budget en points ou HTG');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
