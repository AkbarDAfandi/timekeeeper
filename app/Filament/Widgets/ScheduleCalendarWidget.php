<?php

namespace App\Filament\Widgets;

use App\Models\Global_presets;
use App\Models\Schedule;
use App\Models\Templates;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ScheduleCalendarWidget extends FullCalendarWidget
{
    protected static bool $isDiscovered = false;
    protected string $view = 'filament.widgets.schedule-calendar-widget';

    public array $pendingUpdates = [];

    public function config(): array
    {
        return [
            'initialView' => 'timeGridWeek',
            'selectable' => true,
            'editable' => true,
            'nowIndicator' => true,
            'headerToolbar' => [
                'left' => '',
                'center' => 'title',
                'right' => '',
            ],
            'slotLabelFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'eventTimeFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'slotMinTime' => '06:00:00',
            'slotMaxTime' => '17:00:00',
            'expandRows' => true,
            'height' => 'auto',
            'slotDuration' => '00:30:00',
            'slotLabelInterval' => '00:30:00',
            'allDaySlot' => false,
        ];
    }

    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource = null, ?array $newResource = null): bool
    {
        $id = (string) explode('-', $event['id'])[0];
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return false;
        }

        $newStartTime = Carbon::parse($event['start'])->format('H:i:s');
        $newEndTime = Carbon::parse($event['end'])->format('H:i:s');

        $updates = $this->pendingUpdates;
        if ($schedule->start_time === $newStartTime && $schedule->end_time === $newEndTime) {
            unset($updates[$id]);
        } else {
            $updates[$id] = [
                'start_time' => $newStartTime,
                'end_time' => $newEndTime,
            ];
        }

        $this->pendingUpdates = $updates;
        $this->dispatch('filament-fullcalendar--refresh');

        return true;
    }

    public function refreshEvents(): void
    {
        // Nullified. Automated calendar syncing destroyed.
    }

    public function onEventClick(array $info): void
    {
        $rawId = $info['event']['id'] ?? $info['id'];
        $id = (string) explode('-', $rawId)[0];

        $this->record = Schedule::findOrFail($id);

        $this->mountAction('edit');
    }

    protected function headerActions(): array
    {
        return [
            Action::make('save_changes')
                ->label('Save Changes')
                ->color('success')
                ->visible(fn () => count($this->pendingUpdates) > 0)
                ->action(function () {
                    foreach ($this->pendingUpdates as $id => $data) {
                        Schedule::where('id', $id)->update($data);
                    }
                    $this->pendingUpdates = [];
                    $this->dispatch('filament-fullcalendar--refresh');
                }),

            Action::make('manual_refresh')
                ->label('Refresh')
                ->color('gray')
                ->action(fn () => $this->dispatch('filament-fullcalendar--refresh')),
            CreateAction::make()
                ->model(Schedule::class)
                ->mountUsing(function (Schema $form, array $arguments) {
                    $start = isset($arguments['start']) ? Carbon::parse($arguments['start']) : null;
                    $end = isset($arguments['end']) ? Carbon::parse($arguments['end']) : null;

                    $form->fill([
                        'start_time' => $start ? $start->format('H:i') : null,
                        'end_time' => $end ? $end->format('H:i') : null,
                        'days_of_week' => $start ? [(string) $start->dayOfWeek] : [],
                    ]);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();
                    $data['tenant_id'] = \Filament\Facades\Filament::getTenant()->id;
                    $data['global_preset_id'] ??= 1;
                    $data['tenant_template_id'] ??= 1;
                    return $data;
                })
                ->schema($this->getScheduleFormSchema()),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit Schedule')
                ->icon('heroicon-o-pencil')
                ->modalHeading('Edit Schedule')
                ->form(fn (Schema $form) => $form->schema($this->getScheduleFormSchema()))
                ->fillForm(function () {
                    $data = $this->record?->toArray() ?? [];
                    $data['audio_source'] = match (true) {
                        !empty($data['cached_audio_path']) => 'custom',
                        !empty($data['global_preset_id']) => 'global',
                        !empty($data['tenant_template_id']) => 'template',
                        default => null,
                    };
                    return $data;
                })
                ->action(function (array $data): void {
                    $data['days_of_week'] = $data['days_of_week'] ?? [];
                    $this->record->update($data);
                    $this->dispatch('filament-fullcalendar--refresh');
                }),
            DeleteAction::make(),
        ];
    }

    // Reuse the form schema to keep code DRY
    protected function getScheduleFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->required(),

            TimePicker::make('start_time')
                ->native(false)
                ->displayFormat('H:i')
                ->seconds(false)
                ->required(),

            TimePicker::make('end_time')
                ->native(false)
                ->displayFormat('H:i')
                ->seconds(false)
                ->required(),

            CheckboxList::make('days_of_week')
                ->options([
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                    0 => 'Sunday',
                ])
                ->columns(2)
                ->required()
                ->default([]),

            Select::make('audio_source')
                ->label('Audio Source Selection')
                ->options([
                    'global' => 'Global Preset',
                    'template' => 'Tenant Template',
                    'custom' => 'Custom Upload',
                ])
                ->live()
                ->dehydrated(false) // Prevents Filament from attempting to save this virtual column
                ->afterStateUpdated(function (Set $set) {
                    // Purge dormant states on switch
                    $set('global_preset_id', null);
                    $set('tenant_template_id', null);
                    $set('cached_audio_path', null);
                }),

            Select::make('global_preset_id')
                ->label('Select Global Preset')
                ->options(Global_presets::pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('audio_source') === 'global')
                ->required(fn (Get $get) => $get('audio_source') === 'global'),

            Select::make('tenant_template_id')
                ->label('Select Tenant Template')
                ->options(Templates::where('tenant_id', \Filament\Facades\Filament::getTenant()->id)->pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('audio_source') === 'template')
                ->required(fn (Get $get) => $get('audio_source') === 'template'),

            FileUpload::make('cached_audio_path')
                ->label('Custom Audio Upload')
                ->disk('public')
                ->directory('tenant-audio')
                    ->maxSize(15360)
                ->visible(fn (Get $get) => $get('audio_source') === 'custom')
                ->required(fn (Get $get) => $get('audio_source') === 'custom'),
                ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $schedules = Schedule::where('is_active', true)->get();
        $events = [];

        $period = \Carbon\CarbonPeriod::create($fetchInfo['start'], $fetchInfo['end']);

        foreach ($period as $date) {
            $currentDayOfWeek = $date->dayOfWeek;

            foreach ($schedules as $schedule) {
                $validDays = is_array($schedule->days_of_week) ? $schedule->days_of_week : json_decode($schedule->days_of_week, true) ?? [];

                if (in_array($currentDayOfWeek, $validDays)) {
                    $key = (string) $schedule->id;

                    $startTime = $this->pendingUpdates[$key]['start_time'] ?? $schedule->start_time;
                    $endTime = $this->pendingUpdates[$key]['end_time'] ?? $schedule->end_time;

                    $events[] = [
                        'id' => $schedule->id . '-' . $date->format('Ymd'),
                        'title' => $schedule->title,
                        'start' => $date->copy()->setTimeFromTimeString($startTime)->toIso8601String(),
                        'end' => $date->copy()->setTimeFromTimeString($endTime)->toIso8601String(),
                    ];
                }
            }
        }

        return $events;
    }
}
