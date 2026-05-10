<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveResource\Pages;

use App\Filament\Resources\LiveResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLive extends CreateRecord
{
    protected static string $resource = LiveResource::class;
}
