<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class adhoc_announcements extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiggered_by',
        'message',
        'played_at'
    ];
}
