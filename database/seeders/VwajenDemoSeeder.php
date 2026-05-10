<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\CivicAction;
use App\Models\CooperationProject;
use App\Models\CorruptionReport;
use App\Models\Course;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Live;
use App\Models\Membership;
use App\Models\MuseumCategory;
use App\Models\MuseumEntry;
use App\Models\Post;
use App\Models\Project;
use App\Models\Quiz;
use App\Models\Report;
use App\Models\User;
use App\Models\Video;
use App\Models\Vote;
use App\Models\VoteOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class VwajenDemoSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('user', 'web');

        $admin = User::updateOrCreate(
            ['email' => 'admin@vwajen.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('Admin@12345'),
                'status' => 'active',
                'is_admin' => true,
                'is_verified' => true,
            ]
        );
        $admin->assignRole('admin');

        $member = User::updateOrCreate(
            ['email' => 'membre@vwajen.com'],
            [
                'name' => 'Membre Demo',
                'username' => 'membre_demo',
                'password' => Hash::make('Member@12345'),
                'status' => 'active',
                'is_admin' => false,
                'is_verified' => true,
                'location' => 'Port-au-Prince',
            ]
        );
        $member->assignRole('user');

        $catEducation = Category::updateOrCreate(
            ['slug' => 'education-citoyenne'],
            ['name' => 'Education citoyenne', 'type' => 'education', 'color' => '#2563EB']
        );
        $catContent = Category::updateOrCreate(
            ['slug' => 'realite-terrain'],
            ['name' => 'Réalité du terrain', 'type' => 'content', 'color' => '#16A34A']
        );
        $catEvent = Category::updateOrCreate(
            ['slug' => 'mobilisation-locale'],
            ['name' => 'Mobilisation locale', 'type' => 'event', 'color' => '#F59E0B']
        );

        $course = Course::updateOrCreate(
            ['slug' => 'leadership-citoyen-1'],
            [
                'category_id' => $catEducation->id,
                'created_by' => $admin->id,
                'title' => 'Leadership citoyen - Niveau 1',
                'description' => 'Bases du leadership communautaire et organisation locale.',
                'level' => 'debutant',
                'type' => 'leadership',
                'duration_minutes' => 120,
                'is_published' => true,
                'is_featured' => true,
                'is_free' => true,
                'points_reward' => 25,
            ]
        );

        Lesson::updateOrCreate(
            ['course_id' => $course->id, 'order' => 1],
            [
                'title' => 'Rôle du citoyen responsable',
                'description' => 'Comprendre sa responsabilité dans la communauté.',
                'content' => 'Contenu pédagogique sur les valeurs civiques.',
                'duration_minutes' => 20,
                'is_published' => true,
            ]
        );

        Lesson::updateOrCreate(
            ['course_id' => $course->id, 'order' => 2],
            [
                'title' => 'Organiser une action locale',
                'description' => 'Méthodologie de planification communautaire.',
                'content' => 'Feuille de route pour planifier une action citoyenne.',
                'duration_minutes' => 25,
                'is_published' => true,
            ]
        );

        Quiz::updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Quiz final Leadership 1'],
            ['description' => 'Validation interne du module', 'pass_score' => 70, 'time_limit_minutes' => 20]
        );

        Video::updateOrCreate(
            ['title' => 'Nettoyage quartier Delmas - action rapide'],
            [
                'user_id' => $member->id,
                'category_id' => $catContent->id,
                'video_url' => 'https://example.com/videos/nettoyage-delmas.mp4',
                'thumbnail_url' => 'https://example.com/thumbs/nettoyage-delmas.jpg',
                'duration_seconds' => 48,
                'status' => 'published',
                'content_type' => 'terrain',
                'views_count' => 120,
                'likes_count' => 30,
                'comments_count' => 8,
                'algorithm_score' => 89.5,
                'hashtags' => ['#citoyen', '#quartier', '#action'],
            ]
        );

        Live::updateOrCreate(
            ['title' => 'Débat public: jeunesse et gouvernance'],
            [
                'user_id' => $admin->id,
                'description' => 'Discussion ouverte sur la participation des jeunes.',
                'type' => 'debat',
                'status' => 'scheduled',
                'scheduled_at' => now()->addDays(2),
                'stream_key' => Str::uuid()->toString(),
            ]
        );

        Event::updateOrCreate(
            ['slug' => 'forum-citoyen-pap-2026'],
            [
                'created_by' => $admin->id,
                'category_id' => $catEvent->id,
                'title' => 'Forum citoyen PAP 2026',
                'description' => 'Rencontre de coordination nationale des cellules locales.',
                'type' => 'national',
                'status' => 'published',
                'location' => 'Port-au-Prince',
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(10)->addHours(4),
                'max_participants' => 500,
                'is_featured' => true,
            ]
        );

        CivicAction::updateOrCreate(
            ['slug' => 'nettoyage-cite-soleil-2026'],
            [
                'created_by' => $member->id,
                'title' => 'Nettoyage citoyen Cité Soleil',
                'description' => 'Action communautaire de salubrité.',
                'type' => 'nettoyage',
                'status' => 'planned',
                'location' => 'Cité Soleil',
                'latitude' => 18.5908,
                'longitude' => -72.3330,
                'action_date' => now()->addDays(5),
                'participants_needed' => 50,
            ]
        );

        CooperationProject::updateOrCreate(
            ['title' => 'Partenariat tech Sénégal – Haïti'],
            [
                'user_id' => $admin->id,
                'description' => 'Mise en réseau de jeunes développeurs et échanges de bonnes pratiques.',
                'countries' => 'Sénégal, Haïti',
                'sector' => 'technologie',
                'listing_type' => 'collaboration',
                'contact_email' => 'contact@vwajen.com',
                'organization' => 'VwaJèn / GJKA',
                'is_published' => true,
            ]
        );

        CooperationProject::updateOrCreate(
            ['title' => 'Recherche formateurs en éducation civique'],
            [
                'user_id' => $member->id,
                'description' => 'Besoin de volontaires pour animer des ateliers dans les communes.',
                'countries' => 'Haïti',
                'sector' => 'education',
                'listing_type' => 'job',
                'organization' => 'Cellule Delmas',
                'is_published' => true,
            ]
        );

        $vote = Vote::updateOrCreate(
            ['title' => 'Priorité nationale pour les 12 prochains mois'],
            [
                'created_by' => $admin->id,
                'description' => 'Consultation interne — données de démonstration.',
                'status' => 'active',
                'is_published' => true,
                'is_anonymous' => false,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'total_votes_count' => 32,
            ]
        );

        VoteOption::updateOrCreate(
            ['vote_id' => $vote->id, 'order' => 1],
            ['label' => 'Éducation & jeunesse', 'description' => null, 'votes_count' => 14, 'percentage' => 43.75, 'order' => 1]
        );
        VoteOption::updateOrCreate(
            ['vote_id' => $vote->id, 'order' => 2],
            ['label' => 'Sécurité alimentaire', 'description' => null, 'votes_count' => 10, 'percentage' => 31.25, 'order' => 2]
        );
        VoteOption::updateOrCreate(
            ['vote_id' => $vote->id, 'order' => 3],
            ['label' => 'Environnement & climat', 'description' => null, 'votes_count' => 8, 'percentage' => 25.00, 'order' => 3]
        );

        Project::updateOrCreate(
            ['title' => 'Bibliothèque mobile — quartier Nord'],
            [
                'creator_id' => $member->id,
                'description' => 'Accès gratuit à des livres et ateliers de lecture pour les enfants.',
                'is_published' => true,
                'is_featured' => true,
                'supports_count' => 12,
                'comments_count' => 2,
                'category' => 'educatif',
                'status' => 'published',
            ]
        );

        Post::updateOrCreate(
            ['user_id' => $member->id, 'text' => 'Première publication démo — ansanm nou pi fò! #VwaJèn'],
            [
                'type' => 'text',
                'is_published' => true,
                'likes_count' => 8,
                'comments_count' => 1,
            ]
        );

        $museumCat = MuseumCategory::updateOrCreate(
            ['slug' => 'leaders-revolutionnaires'],
            ['name' => 'Leaders révolutionnaires haïtiens', 'sort_order' => 1, 'is_active' => true]
        );

        MuseumEntry::updateOrCreate(
            ['slug' => 'toussaint-louverture-demo'],
            [
                'museum_category_id' => $museumCat->id,
                'name' => 'Toussaint Louverture',
                'description' => 'Figure de l’indépendance haïtienne — entrée de démonstration pour le Musée.',
                'is_featured' => true,
                'is_published' => true,
                'views_count' => 240,
            ]
        );

        CorruptionReport::firstOrCreate(
            ['title' => 'Signalement démo — interface (données fictives)'],
            [
                'anonymous_token' => Str::random(64),
                'category' => 'niveau_local_communal',
                'description' => 'Description fictive pour tester les écrans. Aucune accusation réelle.',
                'documents' => null,
                'location' => 'Département de l’Ouest',
                'period' => '2025',
                'status' => 'pending',
            ]
        );

        Membership::updateOrCreate(
            ['user_id' => $member->id],
            [
                'type' => 'militant',
                'status' => 'active',
                'department' => 'Ouest',
                'commune' => 'Port-au-Prince',
                'approved_at' => now()->subDays(3),
                'approved_by' => $admin->id,
            ]
        );

        $ad = Advertisement::updateOrCreate(
            ['title' => 'Campagne: Je vote local'],
            [
                'created_by' => $admin->id,
                'description' => 'Sensibilisation à la participation citoyenne locale.',
                'image_url' => 'https://example.com/ads/vote-local.jpg',
                'link_url' => 'https://vwajen.ht/campagnes/vote-local',
                'type' => 'civic_campaign',
                'placement' => 'feed',
                'status' => 'active',
                'start_date' => now()->subDay(),
                'end_date' => now()->addDays(15),
            ]
        );

        $demoVideo = Video::query()->first();
        if ($demoVideo !== null) {
            Report::firstOrCreate(
                [
                    'reporter_id' => $member->id,
                    'reportable_type' => Video::class,
                    'reportable_id' => $demoVideo->id,
                    'reason' => 'desinformation',
                ],
                ['status' => 'pending']
            );
        }

        $this->command->info('VwaJèn — seed démo terminé.');
        $this->command->info('Comptes : admin@vwajen.com / Admin@12345 | membre@vwajen.com / Member@12345');
        $this->command->info('Contenu démo : coopération, vote, projet, post, musée, signalement, publicité : '.$ad->title);
        $this->command->info('Base locale : php artisan migrate:fresh --seed');
    }
}
