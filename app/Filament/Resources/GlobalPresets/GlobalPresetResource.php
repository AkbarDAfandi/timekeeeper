<?php

namespace App\Filament\Resources\GlobalPresets;

use App\Filament\Resources\GlobalPresets\Pages\CreateGlobalPreset;
use App\Filament\Resources\GlobalPresets\Pages\EditGlobalPreset;
use App\Filament\Resources\GlobalPresets\Pages\ListGlobalPresets;
use App\Models\Global_presets;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GlobalPresetResource extends Resource
{
    protected static ?string $model = Global_presets::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    protected static ?string $navigationLabel = 'Global Presets';

    protected static ?string $modelLabel = 'Global Preset';

    protected static ?string $pluralModelLabel = 'Global Presets';

    protected static ?int $navigationSort = 20;

    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        return auth()->user()->role === 'superadmin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Preset Name')
                    ->required()
                    ->maxLength(255),

                Select::make('category')
                    ->label('Category')
                    ->options([
                        'jingle' => 'Jingle',
                        'bell' => 'Bell',
                        'announcement' => 'Announcement',
                        'chime' => 'Chime',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->searchable()
                    ->preload(),

                FileUpload::make('audio_path')
                    ->label('Audio File')
                    ->disk('public')
                    ->directory('global/presets')
                    ->maxSize(15360)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->badge()
                    ->sortable(),

                TextColumn::make('audio_path')
                    ->label('Audio')
                    ->limit(30)
                    ->copyable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
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
            'index' => ListGlobalPresets::route('/'),
            'create' => CreateGlobalPreset::route('/create'),
            'edit' => EditGlobalPreset::route('/{record}/edit'),
        ];
    }
}
