<?php

namespace App\Filament\Resources\GlobalPresets\Pages;

use App\Filament\Resources\GlobalPresets\GlobalPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlobalPresets extends ListRecords
{
    protected static string $resource = GlobalPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
