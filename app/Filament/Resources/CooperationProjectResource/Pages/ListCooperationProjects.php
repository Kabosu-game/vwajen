<?php

namespace App\Filament\Resources\CooperationProjectResource\Pages;

use App\Filament\Resources\CooperationProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCooperationProjects extends ListRecords
{
    protected static string $resource = CooperationProjectResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
