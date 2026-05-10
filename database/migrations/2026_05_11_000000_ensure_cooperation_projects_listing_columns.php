<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent : ajoute user_id + listing_type si la table existe encore sans ces colonnes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cooperation_projects')) {
            return;
        }

        if (! Schema::hasColumn('cooperation_projects', 'user_id')) {
            Schema::table('cooperation_projects', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('user_id');
            });
        }

        if (! Schema::hasColumn('cooperation_projects', 'listing_type')) {
            Schema::table('cooperation_projects', function (Blueprint $table) {
                $table->string('listing_type', 32)->default('collaboration')->after('sector');
                $table->index(['listing_type', 'is_published']);
            });
        }
    }

    public function down(): void
    {
        //
    }
};
