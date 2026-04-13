<?php

namespace App\Filament\Resources\CivicActionResource\Pages;

use App\Filament\Resources\CivicActionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCivicActions extends ListRecords
{
    protected static string $resource = CivicActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
