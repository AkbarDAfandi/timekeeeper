<?php

namespace App\Filament\Resources\GlobalPresets\Pages;

use App\Filament\Resources\GlobalPresets\GlobalPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobalPreset extends EditRecord
{
    protected static string $resource = GlobalPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
