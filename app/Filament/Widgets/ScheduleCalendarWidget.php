<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Carbon\Carbon;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ScheduleCalendarWidget extends FullCalendarWidget
{
    protected static bool $isDiscovered = false;

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
        ];
    }

    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource = null, ?array $newResource = null): bool
    {
        $id = (string) explode('-', $event['id'])[0];

        // Override Livewire array mutation blindness
        $updates = $this->pendingUpdates;
        $updates[$id] = [
            'start_time' => \Carbon\Carbon::parse($event['start'])->format('H:i:s'),
            'end_time' => \Carbon\Carbon::parse($event['end'])->format('H:i:s'),
        ];
        $this->pendingUpdates = $updates;

        // Force UI to sync with new Livewire state
        $this->dispatch('filament-fullcalendar--refresh');

        return true;
    }

    public function refreshEvents(): void
    {
        // Nullified. Automated calendar syncing destroyed.
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
                        \App\Models\Schedule::where('id', $id)->update($data);
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
                        'start_time' => $start ? $start->format('H:i:s') : null,
                        'end_time' => $end ? $end->format('H:i:s') : null,
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
            EditAction::make()->schema($this->getScheduleFormSchema()),
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
                ->default([])
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
                $validDays = $schedule->days_of_week ?? [];

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
