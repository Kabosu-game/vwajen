<?php

namespace App\Filament\Resources\MuseumEntryResource\Pages;

use App\Filament\Resources\MuseumEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMuseumEntry extends EditRecord
{
    protected static string $resource = MuseumEntryResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
