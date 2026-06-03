<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Illuminate\Support\Collection;
use Carbon\CarbonPeriod;

class DashboardScheduleCalendar extends FullCalendarWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

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
        // Nullified. Click interactions destroyed.
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $schedules = Schedule::where('is_active', true)->get();
        $events = [];
        $period = CarbonPeriod::create($fetchInfo['start'], $fetchInfo['end']);

        foreach ($period as $date) {
            $currentDayOfWeek = $date->dayOfWeek;

            foreach ($schedules as $schedule) {
                $validDays = $schedule->days_of_week ?? [];

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
