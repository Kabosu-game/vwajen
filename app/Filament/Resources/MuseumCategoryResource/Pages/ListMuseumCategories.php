<?php

declare(strict_types=1);

namespace App\Filament\Resources\MuseumCategoryResource\Pages;

use App\Filament\Resources\MuseumCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMuseumCategories extends ListRecords
{
    protected static string $resource = MuseumCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
