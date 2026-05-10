<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveResource\Pages;

use App\Filament\Resources\LiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLives extends ListRecords
{
    protected static string $resource = LiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
