<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withPivot('active')
                    ->withTimestamps();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()
                    ->where('name', $roleName)
                    ->where('roles.active', true)
                    ->where('role_user.active', true)
                    ->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()
                    ->whereIn('name', $roles)
                    ->where('roles.active', true)
                    ->where('role_user.active', true)
                    ->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}