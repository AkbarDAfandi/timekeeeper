<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Events\SchedulesUpdated;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'tenant_id',
        'created_by',
        'title',
        'start_time',
        'end_time',
        'days_of_week',
        'is_active',
        'is_override',
        'global_preset_id',
        'tenant_template_id',
        'custom_text_addition',
        'cached_audio_path'
    ];

    protected $attributes = [
        'days_of_week' => '[]',
        'is_active' => true,
        'is_override' => false,
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
        'is_override' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($schedule) {
            if (auth()->check() && empty($schedule->tenant_id)) {
                $schedule->tenant_id = \Filament\Facades\Filament::getTenant()->id;
                $schedule->created_by = auth()->id();
            }
        });

        static::saved(function ($schedule) {
            if (!empty($schedule->tenant_id)) {
                event(new SchedulesUpdated($schedule->tenant_id));
            }
        });

        static::deleted(function ($schedule) {
            if (!empty($schedule->tenant_id)) {
                event(new SchedulesUpdated($schedule->tenant_id));
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function globalPreset()
    {
        return $this->belongsTo(Global_presets::class);
    }

    public function tenantTemplate()
    {
        return $this->belongsTo(Templates::class, 'tenant_template_id');
    }
}
