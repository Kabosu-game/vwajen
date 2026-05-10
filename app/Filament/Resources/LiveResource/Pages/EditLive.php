<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveResource\Pages;

use App\Filament\Resources\LiveResource;
use Filament\Resources\Pages\EditRecord;

class EditLive extends EditRecord
{
    protected static string $resource = LiveResource::class;
}
