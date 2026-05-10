<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;

/**
 * Liste exhaustive des écrans Filament (dérivée du panneau),
 * pour éviter qu’aucune ressource ne manque au tableau de bord.
 */
class CompleteAdminIndexWidget extends Widget
{
    protected static string $view = 'filament.widgets.complete-admin-index-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 5;

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $panel = Filament::getCurrentPanel();
        $rows = [];

        foreach ($panel->getResources() as $resourceClass) {
            if (! is_subclass_of($resourceClass, Resource::class)) {
                continue;
            }
            if (! $resourceClass::canViewAny()) {
                continue;
            }

            $group = $resourceClass::getNavigationGroup() ?? 'Sans groupe';
            $rows[] = [
                'group' => $group,
                'label' => $resourceClass::getNavigationLabel(),
                'url' => $resourceClass::getUrl('index'),
            ];
        }

        usort(
            $rows,
            static fn (array $a, array $b): int => [$a['group'], $a['label']] <=> [$b['group'], $b['label']],
        );

        return [
            'rows' => $rows,
            'count' => count($rows),
        ];
    }
}
