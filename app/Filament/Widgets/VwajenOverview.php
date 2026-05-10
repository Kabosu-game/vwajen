<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Advertisement;
use App\Models\Badge;
use App\Models\Certification;
use App\Models\CivicAction;
use App\Models\Comment;
use App\Models\CooperationProject;
use App\Models\CorruptionReport;
use App\Models\Course;
use App\Models\EngagementPoint;
use App\Models\Event;
use App\Models\Category;
use App\Models\Live;
use App\Models\LiveGift;
use App\Models\LiveGuestInvitation;
use App\Models\LiveMessage;
use App\Models\Membership;
use App\Models\MuseumCategory;
use App\Models\MuseumEntry;
use App\Models\Post;
use App\Models\Project;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Report;
use App\Models\SavedContent;
use App\Models\User;
use App\Models\Video;
use App\Models\Vote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Notifications\DatabaseNotification;

class VwajenOverview extends BaseWidget
{
    /** Affiché en premier sur le tableau de bord */
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Utilisateurs', (string) User::count())
                ->description('Comptes inscrits')
                ->color('gray'),
            Stat::make('Cours publiés', (string) Course::where('is_published', true)->count())
                ->description('Pôle Éducation')
                ->color('success'),
            Stat::make('Quiz actifs', (string) Quiz::where('is_active', true)->count())
                ->description('Questionnaires disponibles')
                ->color('primary'),
            Stat::make('Certifications délivrées', (string) Certification::count())
                ->description('Diplômes enregistrés')
                ->color('success'),
            Stat::make('Tentatives de quiz', (string) QuizAttempt::count())
                ->description('Historique des passages')
                ->color('primary'),
            Stat::make('Badges actifs', (string) Badge::where('is_active', true)->count())
                ->description('À attribuer aux membres')
                ->color('warning'),
            Stat::make('Vidéos publiées', (string) Video::where('status', 'published')->count())
                ->description('Flux & contenus')
                ->color('success'),
            Stat::make('Posts publiés', (string) Post::where('is_published', true)->count())
                ->description('Mur citoyen')
                ->color('primary'),
            Stat::make('Publicités actives', (string) Advertisement::where('status', 'active')->count())
                ->description('Communication')
                ->color('primary'),
            Stat::make('Événements publiés', (string) Event::where('status', 'published')->count())
                ->description('Mobilisation')
                ->color('warning'),
            Stat::make('Actions citoyennes', (string) CivicAction::whereIn('status', ['planned', 'active'])->count())
                ->description('En préparation ou en cours')
                ->color('warning'),
            Stat::make('Projets soutenus', (string) Project::count())
                ->description('Initiatives / financement')
                ->color('warning'),
            Stat::make('Lives à venir / en cours', (string) Live::whereIn('status', ['scheduled', 'live'])->count())
                ->description('Plateforme live')
                ->color('danger'),
            Stat::make('Messages de chat (lives)', (string) LiveMessage::count())
                ->description('Historique des chats')
                ->color('gray'),
            Stat::make('Cadeaux envoyés (lives)', (string) LiveGift::count())
                ->description('Total enregistré')
                ->color('gray'),
            Stat::make('Invitations invités (live)', (string) LiveGuestInvitation::count())
                ->description('Tous statuts')
                ->color('gray'),
            Stat::make('Votes / consultations', (string) Vote::where('is_published', true)->count())
                ->description('Votes publiés')
                ->color('primary'),
            Stat::make('Révolutionnaires (musée)', (string) MuseumEntry::where('is_published', true)->count())
                ->description('Fiches publiées')
                ->color('warning'),
            Stat::make('Catégories musée', (string) MuseumCategory::where('is_active', true)->count())
                ->description('Actives')
                ->color('warning'),
            Stat::make('Catégories (contenu)', (string) Category::where('is_active', true)->count())
                ->description('Cours / vidéos / événements')
                ->color('success'),
            Stat::make('Adhésions GJKA actives', (string) Membership::where('status', 'active')->count())
                ->description('Membres à jour')
                ->color('primary'),
            Stat::make('Coopération — projets', (string) CooperationProject::where('is_published', true)->count())
                ->description('Projets Afrique–Haïti publiés')
                ->color('success'),
            Stat::make('Dénonciations à traiter', (string) CorruptionReport::whereIn('status', ['pending', 'under_review'])->count())
                ->description('En attente ou en examen')
                ->color('danger'),
            Stat::make('Commentaires', (string) Comment::count())
                ->description('Total (tous statuts)')
                ->color('gray'),
            Stat::make('Favoris (bibliothèque)', (string) SavedContent::count())
                ->description('Enregistrements sauvegardés')
                ->color('gray'),
            Stat::make('Notifications non lues', (string) DatabaseNotification::query()->whereNull('read_at')->count())
                ->description('Boîte in-app globale')
                ->color('danger'),
            Stat::make('Lignes historique points', (string) EngagementPoint::count())
                ->description('Traçabilité engagement GJKA')
                ->color('primary'),
            Stat::make('Signalements à traiter', (string) Report::where('status', 'pending')->count())
                ->description('Modération utilisateurs')
                ->color('danger'),
        ];
    }
}
