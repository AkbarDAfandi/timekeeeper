<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'mysql'; 
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
}