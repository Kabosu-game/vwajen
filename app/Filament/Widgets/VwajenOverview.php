<?php

namespace App\Filament\Widgets;

use App\Models\CivicAction;
use App\Models\Course;
use App\Models\Event;
use App\Models\Membership;
use App\Models\Report;
use App\Models\User;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VwajenOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Utilisateurs', (string) User::count())
                ->description('Comptes inscrits')
                ->color('primary'),
            Stat::make('Cours publiés', (string) Course::where('is_published', true)->count())
                ->description('Pôle Education')
                ->color('success'),
            Stat::make('Vidéos publiées', (string) Video::where('status', 'published')->count())
                ->description('Pôle Contenu')
                ->color('success'),
            Stat::make('Événements actifs', (string) Event::where('status', 'published')->count())
                ->description('Calendrier citoyen')
                ->color('warning'),
            Stat::make('Actions citoyennes', (string) CivicAction::whereIn('status', ['planned', 'active'])->count())
                ->description('Mobilisation')
                ->color('warning'),
            Stat::make('Adhésions actives', (string) Membership::where('status', 'active')->count())
                ->description('Membres GJKA')
                ->color('primary'),
            Stat::make('Signalements en attente', (string) Report::where('status', 'pending')->count())
                ->description('Modération')
                ->color('danger'),
        ];
    }
}
