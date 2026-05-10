<?php

namespace App\Filament\Resources\MuseumEntryResource\Pages;

use App\Filament\Resources\MuseumEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMuseumEntries extends ListRecords
{
    protected static string $resource = MuseumEntryResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
