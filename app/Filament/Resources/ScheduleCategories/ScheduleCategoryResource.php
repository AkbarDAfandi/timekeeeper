<?php

namespace App\Filament\Resources\ScheduleCategories;

use App\Filament\Resources\ScheduleCategories\Pages\CreateScheduleCategory;
use App\Filament\Resources\ScheduleCategories\Pages\EditScheduleCategory;
use App\Filament\Resources\ScheduleCategories\Pages\ListScheduleCategories;
use App\Models\ScheduleCategory;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ScheduleCategoryResource extends Resource
{
    protected static ?string $model = ScheduleCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Schedule Categories';

    protected static ?string $modelLabel = 'Schedule Category';

    protected static ?string $pluralModelLabel = 'Schedule Categories';

    protected static ?int $navigationSort = 15;
    protected static UnitEnum|string|null $navigationGroup = 'Config';

    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return in_array($user->role, ['superadmin', 'admin']);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->role === 'superadmin') {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereNull('tenant_id')
                ->when($user->tenant_id, fn ($q) => $q->orWhere('tenant_id', $user->tenant_id));
        });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Unique identifier (e.g. class, break, ishoma)'),

                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                ColorPicker::make('color')
                    ->label('Color')
                    ->required()
                    ->default('#6B7280'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('color')
                    ->badge()
                    ->color(fn (string $state): string => $state),

                TextColumn::make('tenant_id')
                    ->label('Scope')
                    ->formatStateUsing(fn (?string $state) => $state ? 'Tenant' : 'Built-in')
                    ->badge()
                    ->color(fn (?string $state) => $state ? 'warning' : 'success'),

                IconColumn::make('is_built_in')
                    ->label('Built-in')
                    ->state(fn ($record) => $record->tenant_id === null)
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tenant_id')
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->visible(fn ($record) => auth()->user()->role === 'superadmin' || $record->tenant_id !== null),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->tenant_id !== null),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScheduleCategories::route('/'),
            'create' => CreateScheduleCategory::route('/create'),
            'edit' => EditScheduleCategory::route('/{record}/edit'),
        ];
    }
}
