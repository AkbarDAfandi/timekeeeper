<?php

namespace App\Filament\Resources\TenantPresets;

use App\Filament\Resources\TenantPresets\Pages\CreateTenantPreset;
use App\Filament\Resources\TenantPresets\Pages\EditTenantPreset;
use App\Filament\Resources\TenantPresets\Pages\ListTenantPresets;
use App\Models\Templates;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TenantPresetResource extends Resource
{
    protected static ?string $model = Templates::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Tenant Presets';

    protected static ?string $modelLabel = 'Tenant Preset';

    protected static ?string $pluralModelLabel = 'Tenant Presets';

    protected static ?int $navigationSort = 30;
    protected static UnitEnum|string|null $navigationGroup = 'Config';

    public static function canAccess(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Preset Name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('content_id')
                    ->label('Content (Indonesian)')
                    ->placeholder('e.g., Waktu menunjukkan pukul {time}. Saatnya untuk {title}.')
                    ->helperText('Use {time} and {title} as placeholders')
                    ->rows(3)
                    ->required(),

                Textarea::make('content_en')
                    ->label('Content (English)')
                    ->placeholder('e.g., It is {time}. Time for {title}.')
                    ->helperText('Use {time} and {title} as placeholders')
                    ->rows(3)
                    ->required(),

                Toggle::make('is_static')
                    ->label('Static Announcement')
                    ->helperText('Static announcements do not use time/title placeholders')
                    ->default(false),

                FileUpload::make('preset_audio_path')
                    ->label('Audio File')
                    ->disk('public')
                    ->directory('tenant/presets')
                    ->maxSize(15360),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('content_id')
                    ->label('Content (ID)')
                    ->limit(50),

                TextColumn::make('content_en')
                    ->label('Content (EN)')
                    ->limit(50),

                IconColumn::make('is_static')
                    ->label('Static')
                    ->boolean(),

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
            'index' => ListTenantPresets::route('/'),
            'create' => CreateTenantPreset::route('/create'),
            'edit' => EditTenantPreset::route('/{record}/edit'),
        ];
    }
}
