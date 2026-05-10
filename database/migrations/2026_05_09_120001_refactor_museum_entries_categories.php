<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('museum_entries', function (Blueprint $table) {
            $table->foreignId('museum_category_id')->nullable()->after('id')->constrained('museum_categories')->nullOnDelete();
        });

        Schema::table('museum_entries', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
        });

        if (Schema::hasColumn('museum_entries', 'biography')) {
            DB::table('museum_entries')->orderBy('id')->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('museum_entries')->where('id', $row->id)->update([
                        'description' => $row->biography ?? null,
                    ]);
                }
            });
        }

        Schema::table('museum_entries', function (Blueprint $table) {
            $cols = [];
            foreach ([
                'category', 'biography', 'political_thought', 'period', 'birth_year',
                'death_year', 'nationality', 'speeches', 'documents', 'videos', 'timeline',
            ] as $c) {
                if (Schema::hasColumn('museum_entries', $c)) {
                    $cols[] = $c;
                }
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }

    public function down(): void
    {
        Schema::table('museum_entries', function (Blueprint $table) {
            $table->dropForeign(['museum_category_id']);
        });

        Schema::table('museum_entries', function (Blueprint $table) {
            $table->dropColumn(['museum_category_id', 'description']);
        });

        Schema::table('museum_entries', function (Blueprint $table) {
            $table->enum('category', [
                'leader_revolutionnaire_haitien',
                'mouvement_historique',
                'pensee_politique_ideologique',
                'personnage_africain',
                'international',
            ])->default('leader_revolutionnaire_haitien');
            $table->text('biography')->nullable();
            $table->text('political_thought')->nullable();
            $table->string('period')->nullable();
            $table->string('birth_year')->nullable();
            $table->string('death_year')->nullable();
            $table->string('nationality')->nullable();
            $table->json('speeches')->nullable();
            $table->json('documents')->nullable();
            $table->json('videos')->nullable();
            $table->json('timeline')->nullable();
        });
    }
};
