<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chat en direct des lives
        Schema::create('live_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->enum('type', ['text', 'gift', 'system'])->default('text');
            $table->string('gift_type')->nullable()->comment('Type de cadeau si type=gift');
            $table->timestamps();

            $table->index(['live_id', 'created_at']);
        });

        // Cadeaux détaillés dans les lives
        Schema::create('live_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('gift_type');
            $table->integer('value')->default(1)->comment('Valeur du cadeau en points');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_gifts');
        Schema::dropIfExists('live_messages');
    }
};
