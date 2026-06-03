<?php

namespace App\Filament\Resources\TenantPresets\Pages;

use App\Filament\Resources\TenantPresets\TenantPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantPreset extends EditRecord
{
    protected static string $resource = TenantPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
