<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Espace de dénonciation de la corruption (ANONYME)
        // Aucune liaison avec le profil utilisateur — sécurité maximale
        Schema::create('corruption_reports', function (Blueprint $table) {
            $table->id();
            // token_anonyme: hash généré côté serveur, jamais lié à l'identité réelle
            $table->string('anonymous_token', 64)->unique()->comment('Token pour suivi anonyme — sans lien avec l\'utilisateur');
            $table->enum('category', [
                'administration_publique',
                'niveau_local_communal',
                'projets_institutions',
                'police_justice',
                'education',
                'sante',
                'autre'
            ])->default('autre');
            $table->string('title', 200);
            $table->text('description');
            $table->json('documents')->nullable()->comment('URLs de documents, photos, vidéos chiffrés');
            $table->string('location')->nullable()->comment('Lieu de la corruption (sans adresse précise)');
            $table->string('period')->nullable()->comment('Période estimée (ex: 2024-2025)');
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['pending', 'under_review', 'verified', 'dismissed'])->default('pending');
            $table->text('moderator_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            // Mesures anti-traçage: pas d'IP stockée, pas de user_id
            $table->timestamps();

            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corruption_reports');
    }
};
