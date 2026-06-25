<?php

namespace App\Filament\Resources\ScheduleCategories\Pages;

use App\Filament\Resources\ScheduleCategories\ScheduleCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateScheduleCategory extends CreateRecord
{
    protected static string $resource = ScheduleCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $isSuperadmin = $user->role === 'superadmin';

        if (!$isSuperadmin) {
            $tenant = Filament::getTenant();
            $data['tenant_id'] = $tenant ? $tenant->id : ($user->tenant_id ?? null);
        }

        if (!isset($data['tenant_id']) || $data['tenant_id'] === '') {
            $data['tenant_id'] = null;
        }

        return $data;
    }
}
