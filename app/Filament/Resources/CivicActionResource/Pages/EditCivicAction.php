<?php

namespace App\Filament\Resources\CivicActionResource\Pages;

use App\Filament\Resources\CivicActionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCivicAction extends EditRecord
{
    protected static string $resource = CivicActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
