<?php

namespace App\Filament\Resources\TenantPresets\Pages;

use App\Filament\Resources\TenantPresets\TenantPresetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantPreset extends CreateRecord
{
    protected static string $resource = TenantPresetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = \Filament\Facades\Filament::getTenant()->id;

        return $data;
    }
}
