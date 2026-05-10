<?php

namespace App\Filament\Resources\LiveGuestInvitationResource\Pages;

use App\Filament\Resources\LiveGuestInvitationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLiveGuestInvitation extends EditRecord
{
    protected static string $resource = LiveGuestInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
