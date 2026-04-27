<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'mysql';
    public $incrementing = true;
    protected $guarded = [];

    protected $fillable = [
        'name'
    ];
}
