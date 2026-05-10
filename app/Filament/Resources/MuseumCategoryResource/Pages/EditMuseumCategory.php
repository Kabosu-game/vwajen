<?php

declare(strict_types=1);

namespace App\Filament\Resources\MuseumCategoryResource\Pages;

use App\Filament\Resources\MuseumCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditMuseumCategory extends EditRecord
{
    protected static string $resource = MuseumCategoryResource::class;
}
