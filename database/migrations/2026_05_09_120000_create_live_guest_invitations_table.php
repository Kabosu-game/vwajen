<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_guest_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invitee_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending | accepted | declined | revoked
            $table->timestamps();

            $table->index(['live_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_guest_invitations');
    }
};
