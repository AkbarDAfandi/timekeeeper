<?php

namespace App\Filament\Resources\ScheduleCategories\Pages;

use App\Filament\Resources\ScheduleCategories\ScheduleCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduleCategory extends EditRecord
{
    protected static string $resource = ScheduleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
