<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotent : évite Unknown column 'gallery' si une ancienne base n’a pas la migration précédente.
     */
    public function up(): void
    {
        if (Schema::hasTable('votes') && ! Schema::hasColumn('votes', 'gallery')) {
            Schema::table('votes', function (Blueprint $table) {
                $table->json('gallery')->nullable()->after('description');
            });
        }

        if (Schema::hasTable('events') && ! Schema::hasColumn('events', 'gallery')) {
            Schema::table('events', function (Blueprint $table) {
                $table->json('gallery')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('votes') && Schema::hasColumn('votes', 'gallery')) {
            Schema::table('votes', function (Blueprint $table) {
                $table->dropColumn('gallery');
            });
        }

        if (Schema::hasTable('events') && Schema::hasColumn('events', 'gallery')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('gallery');
            });
        }
    }
};
