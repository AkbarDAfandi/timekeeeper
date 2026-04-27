<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content_id',
        'content_en',
        'is_static',
        'preset_audio_path'
    ];
}
