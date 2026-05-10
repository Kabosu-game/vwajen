<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminPolesWidget;
use App\Filament\Widgets\CompleteAdminIndexWidget;
use App\Filament\Widgets\VwajenOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    /**
     * Force l’affichage explicite des widgets : stats → raccourcis → index auto exhaustif.
     * Évite qu’un cache ou une découverte partielle fasse « disparaître » des blocs.
     */
    public function getWidgets(): array
    {
        return [
            VwajenOverview::class,
            AdminPolesWidget::class,
            CompleteAdminIndexWidget::class,
        ];
    }

    /**
     * Une colonne : chaque widget prend toute la largeur, tout reste lisible au scroll.
     */
    public function getColumns(): int | string | array
    {
        return 1;
    }

    public function getHeading(): string
    {
        return 'Tableau de bord — VWAJEN';
    }

    public function getSubheading(): ?string
    {
        return '1) Chiffres clés — 2) Raccourcis par pôle — 3) Tableau exhaustif (toutes les ressources Filament). Descendez jusqu’en bas : rien n’est caché dans un menu replié.';
    }
}
