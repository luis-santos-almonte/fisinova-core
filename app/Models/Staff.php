<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;

    public $timestamps = true;

    protected $table = 'staff';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'position_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function staffSchedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor para obtener el nombre completo
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}