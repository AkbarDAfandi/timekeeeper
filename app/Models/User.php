<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasTenants;


// #[Fillable(['name', 'email', 'password', 'role', 'tenant_id'])]
// #[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tenant_id',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'operator']);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        if ($this->role === 'superadmin') {
            return collect();
        }

        return Tenant::where('id', $this->tenant_id)->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->role === 'superadmin') {
            return true;
        }

        return $this->tenant_id === $tenant->id;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isPrimaryAdmin(): bool
    {
        return $this->role === 'admin' && $this->created_by === null;
    }
}
