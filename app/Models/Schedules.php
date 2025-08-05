<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
