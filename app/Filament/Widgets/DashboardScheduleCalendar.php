<?php

namespace App\Filament\Widgets;

use App\Models\Global_presets;
use App\Models\Schedule;
use App\Models\Templates;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Illuminate\Support\Collection;
use Carbon\CarbonPeriod;

class DashboardScheduleCalendar extends FullCalendarWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.dashboard-schedule-calendar';

    public function config(): array
    {
        return [
            'initialView' => 'timeGridDay', // Focus on current daily playback
            'headerToolbar' => [
                'left' => '',
                'center' => 'title',
                'right' => '',
            ],
            'editable' => false,
            'selectable' => false,
            'clickToCreate' => false,
            'nowIndicator' => true,
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
            'allDaySlot' => false,
        ];
    }

    protected function headerActions(): array
    {
        return [];
    }

    public function onEventClick(array $info): void
    {
        $rawId = $info['event']['id'] ?? $info['id'];
        $id = (string) explode('-', $rawId)[0];

        $this->record = Schedule::findOrFail($id);

        $this->mountAction('view');
    }

    protected function modalActions(): array
    {
        return [
            Action::make('view')
                ->label('View Schedule')
                ->icon('heroicon-o-eye')
                ->modalHeading('Schedule Details')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->form(fn (Schema $form) => $form->schema($this->getScheduleFormSchema())->disabled())
                ->fillForm(fn () => $this->record?->toArray() ?? []),
        ];
    }

    protected function getScheduleFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Title')
                ->required(),

            TimePicker::make('start_time')
                ->label('Start Time')
                ->native(false)
                ->displayFormat('H:i')
                ->seconds(false)
                ->required(),

            TimePicker::make('end_time')
                ->label('End Time')
                ->native(false)
                ->displayFormat('H:i')
                ->seconds(false)
                ->required(),

            CheckboxList::make('days_of_week')
                ->label('Days of Week')
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
                ->required(),

            Select::make('global_preset_id')
                ->label('Global Preset')
                ->options(Global_presets::pluck('name', 'id'))
                ->searchable(),

            Select::make('tenant_template_id')
                ->label('Tenant Template')
                ->options(Templates::pluck('name', 'id'))
                ->searchable(),
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $schedules = Schedule::where('is_active', true)->get();
        $events = [];
        $period = CarbonPeriod::create($fetchInfo['start'], $fetchInfo['end']);

        foreach ($period as $date) {
            $currentDayOfWeek = $date->dayOfWeek;

            foreach ($schedules as $schedule) {
                $validDays = is_array($schedule->days_of_week) ? $schedule->days_of_week : json_decode($schedule->days_of_week, true) ?? [];

                if (in_array($currentDayOfWeek, $validDays)) {
                    $events[] = [
                        'id' => $schedule->id . '-' . $date->format('Ymd'),
                        'title' => $schedule->title,
                        'start' => $date->copy()->setTimeFromTimeString($schedule->start_time)->toIso8601String(),
                        'end' => $date->copy()->setTimeFromTimeString($schedule->end_time)->toIso8601String(),
                    ];
                }
            }
        }
        return $events;
    }
}
