<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adhoc_announcements extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'triggered_by',
        'message',
        'played_at'
    ];
}
