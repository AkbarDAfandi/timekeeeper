<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'created_by',
        'title',
        'start_time',
        'end_time',
        'day_of_week',
        'is_active',
        'is_override',
        'global_preset_id',
        'tenant_template_id',
        'custom_text_addition',
        'cached_audio_path'
    ];
}
