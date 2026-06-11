<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleCategory extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'slug',
        'name',
        'color',
        'tenant_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeBuiltIn($query)
    {
        return $query->whereNull('tenant_id');
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function isBuiltIn(): bool
    {
        return $this->tenant_id === null;
    }

    public static function optionsForTenant(?int $tenantId): array
    {
        return static::whereNull('tenant_id')
            ->when($tenantId, fn ($q) => $q->orWhere('tenant_id', $tenantId))
            ->orderByRaw('tenant_id IS NULL DESC')
            ->orderBy('name')
            ->pluck('name', 'slug')
            ->toArray();
    }

    public static function colorMap(?int $tenantId): array
    {
        return static::whereNull('tenant_id')
            ->when($tenantId, fn ($q) => $q->orWhere('tenant_id', $tenantId))
            ->pluck('color', 'slug')
            ->toArray();
    }
}
