<?php

namespace App\Filament\Resources\TenantPresets\Pages;

use App\Filament\Resources\TenantPresets\TenantPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantPresets extends ListRecords
{
    protected static string $resource = TenantPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
