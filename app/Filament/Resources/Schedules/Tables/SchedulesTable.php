<?php

namespace App\Filament\Resources\Schedules\Tables;

use App\Models\Schedule;
use App\Models\ScheduleCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => self::getCategoryColor($state))
                    ->sortable(),

                TextColumn::make('start_time')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->time('H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(fn () => Schedule::categoryOptions(
                        \Filament\Facades\Filament::getTenant()?->id
                    )),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getCategoryColor(string $slug): string
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $color = ScheduleCategory::where('slug', $slug)
            ->where(function ($q) use ($tenantId) {
                $q->whereNull('tenant_id');
                if ($tenantId) {
                    $q->orWhere('tenant_id', $tenantId);
                }
            })
            ->value('color');

        return $color ?? '#6B7280';
    }
}
