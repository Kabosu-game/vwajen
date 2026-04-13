<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pôle Adhésion GJKA
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['sympathisant', 'membre', 'militant', 'responsable', 'dirigeant'])->default('sympathisant');
            $table->enum('status', ['pending', 'active', 'suspended', 'expired'])->default('pending');
            $table->string('department')->nullable()->comment('Département / Région d\'Haïti');
            $table->string('commune')->nullable();
            $table->string('section')->nullable()->comment('Section communale');
            $table->text('motivation')->nullable();
            $table->string('referral_code')->nullable()->comment('Code de parrainage');
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Points d'engagement (historique)
        Schema::create('engagement_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('action')->comment('Type d\'action: cours_complete, quiz_passe, event_participe, etc.');
            $table->morphs('pointable'); // polymorphique: course, event, action, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_points');
        Schema::dropIfExists('memberships');
    }
};
