<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    public function index()
    {
        // Target your existing display view here
        return view('player.index');
    }

    public function payload(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $currentDay = now()->dayOfWeek;

        $schedules = Schedule::with(['globalPreset', 'tenantTemplate'])
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereJsonContains('days_of_week', $currentDay)
            ->get()
            ->map(function ($schedule) {
                $audioPath = $schedule->cached_audio_path
                    ?? $schedule->globalPreset?->audio_path
                    ?? $schedule->tenantTemplate?->preset_audio_path;

                return [
                    'id' => $schedule->id,
                    'time' => $schedule->start_time,
                    'url' => $audioPath ? Storage::url($audioPath) : null,
                ];
            })
            ->filter(fn ($schedule) => $schedule['url'] !== null)
            ->values();

        return response()->json($schedules);
    }
}
