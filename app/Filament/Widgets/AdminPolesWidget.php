<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\AdvertisementResource;
use App\Filament\Resources\BadgeResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CertificationResource;
use App\Filament\Resources\CivicActionResource;
use App\Filament\Resources\CooperationProjectResource;
use App\Filament\Resources\CommentResource;
use App\Filament\Resources\CorruptionReportResource;
use App\Filament\Resources\CourseResource;
use App\Filament\Resources\DatabaseNotificationResource;
use App\Filament\Resources\EngagementPointResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\LiveGiftResource;
use App\Filament\Resources\LiveGuestInvitationResource;
use App\Filament\Resources\LiveMessageResource;
use App\Filament\Resources\LiveResource;
use App\Filament\Resources\MembershipResource;
use App\Filament\Resources\MuseumCategoryResource;
use App\Filament\Resources\MuseumEntryResource;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\QuizAttemptResource;
use App\Filament\Resources\QuizResource;
use App\Filament\Resources\ReportResource;
use App\Filament\Resources\SavedContentResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\VideoResource;
use App\Filament\Resources\VoteResource;
use Filament\Widgets\Widget;

/**
 * Accès rapide à toutes les zones admin (aligné sur le menu latéral).
 */
class AdminPolesWidget extends Widget
{
    protected static string $view = 'filament.widgets.admin-poles-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Musée des révolutionnaires',
                    'description' => 'Catégories + fiches (photo, description, galerie) comme dans l’app.',
                    'links' => [
                        ['label' => 'Catégories du musée', 'url' => MuseumCategoryResource::getUrl('index')],
                        ['label' => 'Fiches révolutionnaires', 'url' => MuseumEntryResource::getUrl('index')],
                    ],
                ],
                [
                    'title' => 'Éducation & progression',
                    'description' => 'Cours, catégories, quiz, certificats, tentatives, badges.',
                    'links' => [
                        ['label' => 'Cours', 'url' => CourseResource::getUrl('index')],
                        ['label' => 'Catégories (cours / contenu)', 'url' => CategoryResource::getUrl('index')],
                        ['label' => 'Quiz des cours', 'url' => QuizResource::getUrl('index')],
                        ['label' => 'Certifications', 'url' => CertificationResource::getUrl('index')],
                        ['label' => 'Tentatives de quiz', 'url' => QuizAttemptResource::getUrl('index')],
                        ['label' => 'Badges', 'url' => BadgeResource::getUrl('index')],
                    ],
                ],
                [
                    'title' => 'Contenu & diffusion',
                    'description' => 'Mur, vidéos, pubs, directs et activité live.',
                    'links' => [
                        ['label' => 'Publications (mur)', 'url' => PostResource::getUrl('index')],
                        ['label' => 'Vidéos', 'url' => VideoResource::getUrl('index')],
                        ['label' => 'Publicités', 'url' => AdvertisementResource::getUrl('index')],
                        ['label' => 'Lives / directs', 'url' => LiveResource::getUrl('index')],
                        ['label' => 'Chat des lives', 'url' => LiveMessageResource::getUrl('index')],
                        ['label' => 'Cadeaux live', 'url' => LiveGiftResource::getUrl('index')],
                        ['label' => 'Invitations invités (live)', 'url' => LiveGuestInvitationResource::getUrl('index')],
                    ],
                ],
                [
                    'title' => 'Mobilisation',
                    'description' => 'Calendrier, terrain, projets citoyens, votes.',
                    'links' => [
                        ['label' => 'Événements', 'url' => EventResource::getUrl('index')],
                        ['label' => 'Actions citoyennes', 'url' => CivicActionResource::getUrl('index')],
                        ['label' => 'Projets', 'url' => ProjectResource::getUrl('index')],
                        ['label' => 'Votes / consultations', 'url' => VoteResource::getUrl('index')],
                    ],
                ],
                [
                    'title' => 'Coopération & intégrité',
                    'description' => 'Coopération Afrique–Haïti, lutte anti-corruption.',
                    'links' => [
                        ['label' => 'Projets de coopération', 'url' => CooperationProjectResource::getUrl('index')],
                        ['label' => 'Dénonciations corruption', 'url' => CorruptionReportResource::getUrl('index')],
                    ],
                ],
                [
                    'title' => 'Communauté & modération',
                    'description' => 'Échanges, bibliothèque, notifications, signalements.',
                    'links' => [
                        ['label' => 'Commentaires', 'url' => CommentResource::getUrl('index')],
                        ['label' => 'Bibliothèque / favoris', 'url' => SavedContentResource::getUrl('index')],
                        ['label' => 'Notifications (in-app)', 'url' => DatabaseNotificationResource::getUrl('index')],
                        ['label' => 'Signalements', 'url' => ReportResource::getUrl('index')],
                    ],
                ],
                [
                    'title' => 'Adhésion & administration',
                    'description' => 'Membres GJKA, points d’engagement, comptes.',
                    'links' => [
                        ['label' => 'Adhésions GJKA', 'url' => MembershipResource::getUrl('index')],
                        ['label' => 'Historique des points', 'url' => EngagementPointResource::getUrl('index')],
                        ['label' => 'Utilisateurs', 'url' => UserResource::getUrl('index')],
                    ],
                ],
            ],
        ];
    }
}
