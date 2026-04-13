<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\CivicAction;
use App\Models\Course;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Live;
use App\Models\Membership;
use App\Models\Quiz;
use App\Models\Report;
use App\Models\User;
use App\Models\Video;
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

        Report::firstOrCreate([
            'reporter_id' => $member->id,
            'reportable_type' => Video::class,
            'reportable_id' => Video::query()->value('id'),
            'reason' => 'desinformation',
            'status' => 'pending',
        ]);

        $this->command->info('VWAJEN demo seed terminé.');
        $this->command->info('Super admin: admin@vwajen.com / Admin@12345');
        $this->command->info('Membre demo: membre@vwajen.com / Member@12345');
        $this->command->info('Publicité active: '.$ad->title);
    }
}
