<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasActiveToggle;

class Employee extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;

    public $timestamps = true;

    protected $fillable = [
        'firstname',
        'lastname',
        'dni',
        'cellphone',
        'phone',
        'email',
        'address',
        'active',
        'position_id',
        'user_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedules::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}