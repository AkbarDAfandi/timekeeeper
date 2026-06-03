<?php

namespace App\Filament\Pages;

use App\Events\AdhocAudioDispatched;
use App\Models\Adhoc_announcements;
use App\Models\Global_presets;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class InstantAnnouncement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;
    protected static ?string $navigationLabel = 'Instant Announcement';
    protected static ?string $title = 'Broadcast Instant Announcement';
    protected static ?string $slug = 'instant-announcement';
    protected string $view = 'filament.pages.instant-announcement';
    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return in_array(auth()->user()->role, ['superadmin', 'admin', 'operator']);
    }

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('audio_source')
                    ->label('Audio Source')
                    ->options([
                        'global' => 'Global Preset',
                        'custom' => 'Custom Upload',
                    ])
                    ->live()
                    ->required(),

                Select::make('global_preset_id')
                    ->label('Select Global Preset')
                    ->options(Global_presets::pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn ($get) => $get('audio_source') === 'global')
                    ->required(fn ($get) => $get('audio_source') === 'global'),

                FileUpload::make('audio_file')
                    ->label('Upload Audio')
                    ->disk('public')
                    ->directory('adhoc-audio')
                    ->maxSize(15360)
                    ->visible(fn ($get) => $get('audio_source') === 'custom')
                    ->required(fn ($get) => $get('audio_source') === 'custom'),

                Textarea::make('message')
                    ->label('Announcement Message (Optional)')
                    ->placeholder('e.g., Attention all students...')
                    ->rows(3),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $tenantId = \Filament\Facades\Filament::getTenant()->id;

        $audioUrl = null;

        if ($data['audio_source'] === 'global' && !empty($data['global_preset_id'])) {
            $preset = Global_presets::find($data['global_preset_id']);
            $audioUrl = $preset?->audio_path;
        } elseif ($data['audio_source'] === 'custom' && !empty($data['audio_file'])) {
            $audioUrl = Storage::url($data['audio_file']);
        }

        if (!$audioUrl) {
            Notification::make()
                ->title('No audio source selected')
                ->danger()
                ->send();
            return;
        }

        Adhoc_announcements::create([
            'tenant_id' => $tenantId,
            'triggered_by' => auth()->id(),
            'message' => $data['message'] ?? null,
            'played_at' => now(),
        ]);

        event(new AdhocAudioDispatched($tenantId, $audioUrl));

        Notification::make()
            ->title('Announcement broadcasted successfully')
            ->success()
            ->send();

        $this->form->fill([]);
    }
}
