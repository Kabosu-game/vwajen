<?php

declare(strict_types=1);

namespace App\Filament\Resources\MuseumCategoryResource\Pages;

use App\Filament\Resources\MuseumCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMuseumCategory extends CreateRecord
{
    protected static string $resource = MuseumCategoryResource::class;
}
