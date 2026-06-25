<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Tenant;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?int $navigationSort = 30;

    protected static UnitEnum|string|null $navigationGroup = 'Admin';

    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'admin']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->role === 'admin') {
            $query->where('tenant_id', $user->tenant_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('tenant_id')
                    ->label('Tenant')
                    ->options(Tenant::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn () => auth()->user()->role === 'superadmin'),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->confirmed()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state): bool => filled($state)),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),

                Select::make('role')
                    ->options(fn () => static::availableRoles())
                    ->required()
                    ->disabled(fn (?User $record) => $record && $record->id === auth()->id())
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isSuperadmin = auth()->user()?->role === 'superadmin';

        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperadmin),

                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'admin' => 'warning',
                        'operator' => 'info',
                        'player' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'superadmin' => 'Superadmin',
                        'admin' => 'Admin',
                        'operator' => 'Operator',
                        'player' => 'Player',
                    ])
                    ->visible($isSuperadmin),

                SelectFilter::make('tenant')
                    ->relationship('tenant', 'name')
                    ->visible($isSuperadmin),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions(static::tableActions())
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function tableActions(): array
    {
        $user = auth()->user();

        return [
            \Filament\Actions\EditAction::make()
                ->visible(fn (User $record) => static::canEditRecord($user, $record)),

            \Filament\Actions\DeleteAction::make()
                ->visible(fn (User $record) => static::canDeleteRecord($user, $record)),
        ];
    }

    public static function canEditRecord(?User $actor, User $record): bool
    {
        if (! $actor) {
            return false;
        }

        if ($actor->role === 'superadmin') {
            return true;
        }

        if ($actor->role === 'admin') {
            if ($record->id === $actor->id) {
                return true;
            }

            if ($record->role === 'admin' && $record->id !== $actor->id) {
                return $actor->isPrimaryAdmin();
            }

            return true;
        }

        return false;
    }

    public static function canDeleteRecord(?User $actor, User $record): bool
    {
        if (! $actor) {
            return false;
        }

        if ($record->id === $actor->id) {
            return false;
        }

        if ($actor->role === 'superadmin') {
            return $record->role !== 'superadmin' || $record->id !== $actor->id;
        }

        if ($actor->role === 'admin') {
            if ($record->role === 'admin') {
                return $actor->isPrimaryAdmin();
            }

            return true;
        }

        return false;
    }

    public static function availableRoles(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        if ($user->role === 'superadmin') {
            return [
                'superadmin' => 'Superadmin',
                'admin' => 'Admin',
                'operator' => 'Operator',
                'player' => 'Player',
            ];
        }

        return [
            'admin' => 'Admin',
            'operator' => 'Operator',
            'player' => 'Player',
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
