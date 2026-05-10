<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Galerie illimitée (JSON) : tableau d’objets { "type": "image"|"video"|"audio", "url": "..." }.
     */
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->json('gallery')->nullable()->after('description');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->json('gallery')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });
    }
};
