<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['created_by'] = $user->id;

        if ($user->role === 'admin') {
            $data['tenant_id'] = $user->tenant_id;
        }

        return $data;
    }
}
