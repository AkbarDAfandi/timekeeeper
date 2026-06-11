<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Schedule;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->options(fn () => Schedule::categoryOptions(
                        \Filament\Facades\Filament::getTenant()?->id
                    ))
                    ->required()
                    ->native(false)
                    ->default('class'),
            ]);
    }
}
