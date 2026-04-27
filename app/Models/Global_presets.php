<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Global_presets extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'audio_path'
    ];
}
